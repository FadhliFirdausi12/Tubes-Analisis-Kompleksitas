<?php

namespace App\Services;

use RuntimeException;

/**
 * Membaca data penduduk dari CSV BPS: resources/data/penduduk.csv
 *
 * Kolom yang dipakai (berdasarkan URUTAN kolom):
 *   kolom 1 = nama kecamatan
 *   kolom 2 = jumlah penduduk (jiwa)    -> jadi P0
 *   kolom 3 = laju pertumbuhan (%/tahun) -> jadi r
 *
 * Baris bernama "Bandung" = total Kota Bandung.
 * Baris kosong = akhir data; setelahnya hanya catatan kaki dan diabaikan.
 *
 * Tugas class ini hanya MEMBACA data, tidak menghitung apa pun.
 */
class PopulationDataReader
{
    public function all(): array
    {
        $path = resource_path('data/penduduk.csv'); // alamat file di dalam proyek
        if (! is_readable($path)) {
            throw new RuntimeException("File CSV tidak ditemukan: {$path}");
        }

        $handle = fopen($path, 'r');

        // Baris pertama = judul kolom, dilewati. fgetcsv membaca satu baris
        // CSV menjadi array (pemisah koma, tanda kutip ").
        $header = fgetcsv($handle, null, ',', '"', '');
        $rows = [];

        while (($line = fgetcsv($handle, null, ',', '"', '')) !== false) {
            $nama = trim($line[0] ?? '');
            if ($nama === '') {
                break; // baris kosong -> sisanya catatan kaki, berhenti membaca
            }

            $rows[] = [
                'nama'     => $nama === 'Bandung' ? 'Kota Bandung' : $nama, // rename baris total
                'populasi' => (int) $line[1],     // jiwa
                'laju'     => (float) $line[2],   // masih dalam PERSEN (0.87 = 0,87%)
            ];
        }
        fclose($handle);

        // Urutkan agar "Kota Bandung" selalu paling atas (jadi pilihan default dropdown)
        usort($rows, fn ($a, $b) => ($b['nama'] === 'Kota Bandung') <=> ($a['nama'] === 'Kota Bandung'));

        return $rows;
    }

    /** Cari satu wilayah berdasarkan nama; null kalau tidak ada. */
    public function find(string $nama): ?array
    {
        foreach ($this->all() as $row) {
            if ($row['nama'] === $nama) {
                return $row;
            }
        }
        return null;
    }
}