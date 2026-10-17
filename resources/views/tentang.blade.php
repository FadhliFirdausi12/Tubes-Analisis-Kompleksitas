<x-layouts.app title="Tentang Algoritma">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

        {{-- Header halaman --}}
        <p class="flex items-center gap-2 font-mono text-xs text-gray-600">
            <span class="w-3 h-3 bg-brand inline-block"></span> Referensi
        </p>
        <h1 class="text-4xl font-bold tracking-tight mt-3">Tentang algoritmanya</h1>
        <p class="font-mono text-xs leading-5 text-gray-700 mt-3 max-w-md">
            Ringkasan cara kerja kedua pendekatan yang dibandingkan pada halaman Analisis.
        </p>

        {{-- Dua kartu ringkasan --}}
        <section class="mt-8 grid md:grid-cols-2 border border-ink bg-paper">
            <div class="p-6 md:border-r border-ink">
                <h2 class="text-lg font-bold text-iter">O(t) — Iteratif</h2>
                <p class="font-mono text-[11px] leading-5 text-gray-700 mt-4">
                    Mengalikan P₀ dengan (1+r) sebanyak t kali secara berurutan dalam satu loop.
                    Sederhana, tapi waktu tumbuh linear terhadap t.
                </p>
            </div>
            <div class="p-6 border-t md:border-t-0 border-ink">
                <h2 class="text-lg font-bold text-rec md:ml-4">O(log t) — Rekursif</h2>
                <p class="font-mono text-[11px] leading-5 text-gray-700 mt-4 md:ml-4">
                    Memecah pangkat t menjadi setengahnya di tiap panggilan (exponentiation by squaring).
                    Waktu tumbuh jauh lebih lambat terhadap t
                </p>
            </div>
        </section>

        <hr class="mt-10 border-gray-500">

        {{-- Versi iteratif --}}
        <section class="mt-8">
            <h2 class="text-lg font-bold">Versi iteratif</h2>
            <p class="font-mono text-[11px] leading-5 text-gray-700 mt-3 max-w-md">
                Loop sebanyak t kali, mengalikan hasil dengan basis (1+r) di tiap iterasi.
            </p>
<pre class="mt-5 bg-[#333632] text-gray-200 font-mono text-[11px] leading-5 p-5 overflow-x-auto">hasil = 1
untuk i dari 1 sampai t:
   hasil = hasil * (1 + r)
kembalikan P0 * hasil</pre>
        </section>

        <hr class="mt-10 border-gray-500">

        {{-- Versi rekursif --}}
        <section class="mt-8">
            <h2 class="text-lg font-bold">Versi rekursif</h2>
            <p class="font-mono text-[11px] leading-5 text-gray-700 mt-3 max-w-md">
                Memanfaatkan sifat (1+r)^t = ((1+r)^(t/2))², sehingga pangkat besar diselesaikan
                dengan jauh lebih sedikit perkalian.
            </p>
<pre class="mt-5 bg-[#333632] text-gray-200 font-mono text-[11px] leading-5 p-5 overflow-x-auto">fungsi pangkat(basis, t):
  jika t == 0: kembalikan 1
  separuh = pangkat(basis, t // 2)
  jika t genap: kembalikan separuh * separuh
  jika t ganjil: kembalikan separuh * separuh * basis</pre>
        </section>

        <hr class="mt-10 border-gray-500">

        {{-- Sumber data --}}
        <section class="mt-8 pb-8">
            <h2 class="text-lg font-bold">Sumber data</h2>
            <p class="font-mono text-[11px] leading-5 text-gray-700 mt-3 max-w-md">
                Parameter P₀ (populasi awal) dan r (laju pertumbuhan tahunan) diekstrak dari data
                historis BPS/data.go.id untuk satu wilayah, lalu dipakai sebagai konstanta tetap di
                seluruh simulasi — tidak berubah walau ukuran input t divariasikan.
            </p>
        </section>
    </div>
</x-layouts.app>