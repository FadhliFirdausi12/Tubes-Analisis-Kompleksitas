<?php

namespace App\Services;

/**
 * INTI TUGAS: dua cara menghitung proyeksi penduduk
 *
 *   P(t) = P0 * (1 + r)^t
 *
 *   P0 = jumlah penduduk awal (dari CSV BPS)
 *   r  = laju pertumbuhan per tahun, dalam desimal (0,87% -> 0.0087)
 *   t  = berapa tahun ke depan
 *
 * Masalah utamanya: menghitung (1 + r) pangkat t.
 *   - Cara naif (iteratif)  -> t kali perkalian        -> O(t)
 *   - Perpangkatan cepat    -> sekitar log2(t) perkalian -> O(log t)
 */
class PopulationProjection
{
    /**
     * CARA NAIF, O(t).
     * Kalikan (1 + r) sebanyak t kali. t = 10.000 berarti 10.000 perkalian.
     * Memori O(1): hanya butuh satu variabel $faktor.
     */
    public function iterative(float $p0, float $r, int $t): float
    {
        $faktor = 1.0;                  // (1 + r)^0 = 1
        for ($i = 0; $i < $t; $i++) {   // loop berjalan tepat t kali
            $faktor *= 1 + $r;
        }
        return $p0 * $faktor;
    }

    /**
     * CARA CEPAT, O(log t). Ini hanya "pintu masuk"; kerja sebenarnya di power().
     */
    public function fast(float $p0, float $r, int $t): float
    {
        return $p0 * $this->power(1 + $r, $t);
    }

    /**
     * PERPANGKATAN CEPAT (fast exponentiation / exponentiation by squaring), rekursif.
     *
     * Ide: pangkat dipecah jadi setengahnya.
     *   t genap : x^t = (x^(t/2))^2
     *   t ganjil: x^t = x * (x^((t-1)/2))^2
     *   t = 0   : x^0 = 1   (kasus dasar, penghenti rekursi)
     *
     * Contoh x^10 = (x^5)^2, lalu x^5 = x * (x^2)^2, lalu x^2 = (x^1)^2, dst.
     * Karena t dibagi 2 di tiap panggilan, kedalaman rekursi sekitar log2(t).
     * t = 10.000 -> sekitar 14 level, bukan 10.000 langkah.
     *
     * Rekurensi: T(t) = T(t/2) + O(1)  =>  T(t) = O(log t)
     * Memori: O(log t) untuk tumpukan (stack) panggilan rekursif.
     */
    private function power(float $basis, int $t): float
    {
        if ($t === 0) {
            return 1.0;                                   // kasus dasar
        }
        $separuh = $this->power($basis, intdiv($t, 2));   // hitung SEKALI saja, lalu dikuadratkan
        return $t % 2 === 0
            ? $separuh * $separuh                         // t genap
            : $separuh * $separuh * $basis;               // t ganjil: ada sisa satu faktor basis
    }

    /**
     * Mengukur waktu rata-rata SATU panggilan fungsi, dalam milidetik.
     *
     * Kenapa diulang $reps kali lalu dirata-rata?
     * Satu panggilan terlalu singkat (hitungan mikrodetik), mudah terganggu noise
     * sistem. Mengulang 1.000 kali dan membagi rata membuat hasilnya lebih stabil.
     *
     * hrtime(true) = jam resolusi tinggi (nanodetik). Dibagi 1e6 -> milidetik.
     */
    private function measure(callable $fn, int $reps): float
    {
        $start = hrtime(true);
        for ($i = 0; $i < $reps; $i++) {
            $hasil = $fn();
        }
        return (hrtime(true) - $start) / $reps / 1e6;
    }

    /**
     * Satu baris hasil benchmark untuk satu ukuran input t.
     * $iter / $fast = apakah algoritma itu dipilih di checkbox (kalau tidak, null).
     */
    public function benchmark(float $p0, float $r, int $t, bool $iter, bool $fast, int $reps = 1000): array
    {
        // Hitung hasil kedua algoritma sekali untuk memeriksa kebenaran
        $a = $this->iterative($p0, $r, $t);
        $b = $this->fast($p0, $r, $t);

        return [
            't'        => $t,
            'iteratif' => $iter ? $this->measure(fn () => $this->iterative($p0, $r, $t), $reps) : null,
            'rekursif' => $fast ? $this->measure(fn () => $this->fast($p0, $r, $t), $reps) : null,
            'proyeksi' => $b,

            // Kedua algoritma harus menghasilkan angka yang sama. Toleransi 1e-9
            // (relatif) diperlukan karena urutan perkalian berbeda sehingga
            // pembulatan bilangan desimal (floating point) sedikit berbeda.
            'sama'     => abs($a - $b) <= 1e-9 * abs($b),
        ];
    }
}