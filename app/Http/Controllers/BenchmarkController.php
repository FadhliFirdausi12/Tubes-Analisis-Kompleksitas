<?php

namespace App\Http\Controllers;

use App\Services\PopulationDataReader;
use App\Services\PopulationProjection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pengatur alur. Controller TIDAK menghitung dan TIDAK membaca CSV sendiri;
 * ia hanya menerima request, memanggil service yang tepat, lalu membalas.
 *
 * Alur:  route -> controller -> service (reader + projection) -> view / JSON
 */
class BenchmarkController extends Controller
{
    // Tiap ukuran input dijalankan 1.000 kali lalu dirata-rata (lihat measure())
    private const REPS = 1000;

    /** GET /analisis -> menampilkan halaman, dengan daftar wilayah untuk dropdown. */
    public function index(PopulationDataReader $reader)
    {
        $wilayah = $reader->all();

        return view('analisis', [
            'wilayah' => $wilayah,
            'default' => $wilayah[0], // Kota Bandung (sudah diurutkan paling atas)
        ]);
    }

    /** POST /benchmark -> dipanggil tombol "Jalankan Benchmark" lewat fetch (AJAX). */
    public function run(Request $request, PopulationDataReader $reader, PopulationProjection $projection): JsonResponse
    {
        // Validasi input dari browser. Kalau tidak lolos, Laravel otomatis membalas error 422.
        $data = $request->validate([
            'wilayah'  => 'required|string',
            'sizes'    => 'required|array|min:1|max:12',
            // Batas t = 10.000: di atas itu (1+r)^t bisa melebihi batas angka PHP
            // (sekitar 1,8e308) dan menjadi INF. Dengan r maks ~2,13% (Gedebage),
            // batas amannya sekitar t = 33.000, jadi 10.000 aman untuk semua wilayah.
            'sizes.*'  => 'integer|min:1|max:10000',
            'iteratif' => 'required|boolean',
            'rekursif' => 'required|boolean',
        ]);

        // Minimal satu algoritma harus dipilih
        if (! $data['iteratif'] && ! $data['rekursif']) {
            return response()->json(['message' => 'Pilih minimal satu algoritma.'], 422);
        }

        // Ambil P0 dan r wilayah yang dipilih dari CSV
        $w = $reader->find($data['wilayah']);
        if (! $w) {
            return response()->json(['message' => 'Wilayah tidak ditemukan.'], 422);
        }

        // CSV menyimpan persen (0.87), rumus butuh desimal (0.0087)
        $r = $w['laju'] / 100;

        // Jalankan benchmark untuk SETIAP ukuran t (mis. 1, 10, 100, ..., 10000).
        // Kedua algoritma diuji pada t yang sama persis agar perbandingannya adil.
        $rows = array_map(
            fn ($t) => $projection->benchmark($w['populasi'], $r, (int) $t, $data['iteratif'], $data['rekursif'], self::REPS),
            $data['sizes']
        );

        return response()->json(['wilayah' => $w, 'reps' => self::REPS, 'rows' => $rows]);
    }
}