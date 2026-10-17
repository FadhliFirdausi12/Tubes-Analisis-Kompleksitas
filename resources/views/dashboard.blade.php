<x-layouts.app title="Dashboard">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">

        {{-- Hero --}}
        <section>
            <h1 class="text-4xl sm:text-5xl font-bold tracking-tight leading-tight max-w-xl">
                Seberapa jauh pemangkatan cepat mengungguli cara naif?
            </h1>
            <p class="font-mono text-base leading-7 text-ink mt-6 max-w-xl">
                PowRace membandingkan dua cara menghitung proyeksi jumlah penduduk, perpangkatan
                cepat (rekursif) dan perkalian berulang (iteratif), lalu mengukur langsung selisih
                waktunya
            </p>
        </section>

        {{-- Kartu utama --}}
        <section class="mt-12 grid md:grid-cols-[3fr_2fr] border border-ink bg-paper">

            {{-- Model pertumbuhan --}}
            <div class="p-6 sm:p-8 md:border-r border-ink">
                <p class="flex items-center gap-2 font-mono text-[11px] text-gray-700">
                    <span class="w-3 h-3 bg-iter inline-block"></span> Model Pertumbuhan Ekponensial
                </p>

                <div class="mt-6 bg-canvas p-5">
                    <p class="font-mono text-sm">
                        P(t) = <span class="text-iter">P₀</span> × (1 + <span class="text-brand">r</span>)<sup class="text-rec">t</sup>
                    </p>
                    <p class="font-mono text-[11px] leading-5 mt-4 text-gray-700">
                        <span class="text-iter">P₀</span> = populasi awal |
                        <span class="text-brand">r</span> = laju pertumbuhan |
                        <span class="text-rec">t</span> = tahun proyeksi (ukuran input)
                    </p>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('analisis') }}"
                       class="bg-black text-white font-mono text-[11px] font-bold px-4 py-3 border border-black hover:bg-ink">
                        Jalankan Analisis -&gt;
                    </a>
                    <a href="{{ route('tentang') }}"
                       class="bg-paper text-ink font-mono text-[11px] font-bold px-4 py-3 border border-ink hover:bg-canvas">
                        Pelajari Algoritmanya
                    </a>
                </div>
            </div>

            {{-- Kompleksitas --}}
            <div class="p-6 sm:p-8 border-t md:border-t-0 border-ink flex flex-col justify-center">
                <h2 class="text-2xl font-bold">O(log t) vs O(t)</h2>
                <p class="font-mono text-sm leading-6 mt-4">
                    Kompleksitas waktu asimtotik rekursif (fast exponentiation) dibanding iteratif
                    naif, terhadap jumlah tahun proyeksi t.
                </p>
            </div>
        </section>

        {{-- Tiga kartu info --}}
        <section class="mt-8 grid md:grid-cols-3 border border-ink bg-paper">
            <div class="p-5 md:border-r border-ink">
                <p class="text-[11px] uppercase text-brand">Sumber Data</p>
                <h3 class="text-sm font-bold mt-2">BPS &amp; data.go.id</h3>
                <p class="font-mono text-[11px] leading-5 text-gray-800 mt-3">
                    Parameter P₀ dan r diambil dari data historis jumlah penduduk per wilayah,
                    dipakai sebagai konstanta pada seluruh simulasi
                </p>
            </div>

            <div class="p-5 md:border-r border-t md:border-t-0 border-ink">
                <p class="text-[11px] uppercase text-brand">Skala Uji</p>
                <h3 class="text-sm font-bold mt-2">t = 1 s.d. 10.000</h3>
                <p class="font-mono text-[11px] leading-5 text-gray-800 mt-3">
                    Ukuran input diuji pada rentang luas untuk melihat titik di mana perbedaan
                    efisiensi mulai terlihat jelas.
                </p>
            </div>

            <div class="p-5 border-t md:border-t-0 border-ink">
                <p class="text-[11px] uppercase text-brand">Keluaran</p>
                <h3 class="text-sm font-bold mt-2">Tabel &amp; Grafik</h3>
                <p class="font-mono text-[11px] leading-5 text-gray-800 mt-3">
                    Setiap run menghasilkan perbandingan waktu eksekusi berdampingan, siap
                    dianalisis di laporan.
                </p>
            </div>
        </section>
    </div>
</x-layouts.app>