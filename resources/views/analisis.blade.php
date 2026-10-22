{{--
    HALAMAN ANALISIS (view)
    ------------------------------------------------------------------
    Alur halaman ini:
      1. Controller (BenchmarkController@index) mengirim $wilayah dan $default.
      2. Blade merender HTML: form parameter + tempat hasil (tabel & grafik).
      3. JavaScript di bagian bawah mengirim pilihan pengguna ke server
         (POST /benchmark), menerima hasil JSON, lalu menggambar tabel & grafik.
    Perhitungan TIDAK dilakukan di sini, melainkan di PHP (PopulationProjection).
--}}

{{-- <x-layouts.app> = layout bersama (navbar + footer). Isi di dalamnya masuk ke {{ $slot }}. --}}
    <x-layouts.app title="Analisis">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    
            {{-- ===== Judul halaman ===== --}}
            <p class="flex items-center gap-2 font-mono text-xs text-gray-600">
                <span class="w-3 h-3 bg-brand inline-block"></span> Benchmark
            </p>
            <h1 class="text-4xl font-bold tracking-tight mt-3">Jalankan Benchmark</h1>
            <p class="font-mono text-xs leading-5 text-gray-700 mt-3 max-w-md">
                Pilih wilayah, ukuran input, dan algoritma yang ingin diuji. Iteratif dan rekursif dijalankan
                pada ukuran input yang sama persis agar hasilnya bisa dibandingkan secara adil.
            </p>
    
            {{-- ===== Panel parameter: apa yang mau diuji ===== --}}
            <section class="mt-8 border border-ink bg-paper p-5">
                <h2 class="font-bold text-lg">Parameter Pengujian</h2>
    
                <div class="mt-4 flex flex-wrap items-end gap-x-10 gap-y-5">
    
                    {{-- Pilihan wilayah. $wilayah berasal dari CSV (dibaca PopulationDataReader).
                         Wilayah ini menentukan P0 dan r yang dipakai di rumus. --}}
                    <div>
                        <p class="text-xs mb-2">Wilayah</p>
                        <select id="wilayah" class="w-44 border border-ink bg-gray-200 px-3 py-2 font-mono text-sm">
                            @foreach ($wilayah as $w)
                                <option value="{{ $w['nama'] }}">{{ $w['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    {{-- Tombol pengalih: pilih t dari daftar (preset) atau ketik sendiri (manual).
                         Perilakunya diatur oleh fungsi setMode() di JavaScript. --}}
                    <div>
                        <p class="text-xs mb-2">Metode Ukuran Input</p>
                        <div class="flex border border-ink font-mono text-xs">
                            <button id="mode-preset" class="px-4 py-2 bg-ink text-white">Dropdown Preset</button>
                            <button id="mode-manual" class="px-4 py-2 bg-gray-300">Input Manual</button>
                        </div>
                    </div>
    
                    {{-- Ukuran input t (jumlah tahun proyeksi). t adalah "n" dalam analisis kompleksitas.
                         Dropdown preset dan kolom manual bergantian tampil (class "hidden"). --}}
                    <div>
                        <p class="text-xs mb-2">Ukuran Input (t)</p>
                        <select id="size-select" class="w-40 border border-ink bg-gray-200 px-3 py-2 font-mono text-sm">
                            @foreach ([1, 10, 100, 1000, 5000, 10000] as $n)
                                <option value="{{ $n }}" @selected($n === 10000)>{{ number_format($n, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                        {{-- max=10000 sama dengan batas validasi di controller --}}
                        <input id="size-input" type="number" min="1" max="10000" value="10000"
                            class="hidden w-40 border border-ink bg-gray-200 px-3 py-2 font-mono text-sm">
                    </div>
    
                    {{-- Checkbox: algoritma mana yang diuji. Warna biru = iteratif, oranye = rekursif
                         (warna yang sama dipakai di tabel dan grafik). --}}
                    <div>
                        <p class="text-xs mb-2">Algoritma diuji</p>
                        <label class="flex items-center gap-2 font-mono text-xs mb-1">
                            <input id="chk-iter" type="checkbox" checked class="w-4 h-4 accent-ink">
                            <span class="w-3.5 h-3.5 bg-iter inline-block"></span> Iteratif
                        </label>
                        <label class="flex items-center gap-2 font-mono text-xs">
                            <input id="chk-rec" type="checkbox" checked class="w-4 h-4 accent-ink">
                            <span class="w-3.5 h-3.5 bg-rec inline-block"></span> Rekursif
                        </label>
                    </div>
    
                    {{-- Tombol jalankan. Diklik -> fungsi run() di JavaScript.
                         "disabled:opacity-50" membuatnya pudar saat sedang menghitung. --}}
                    <div class="ml-auto text-right">
                        <p class="text-xs mb-3">t = tahun proyeksi (maks. 10.000)</p>
                        <button id="run"
                            class="bg-brand hover:opacity-90 disabled:opacity-50 text-white font-mono px-8 py-2.5">
                            Jalankan Benchmark
                        </button>
                    </div>
                </div>
    
                {{-- Tempat pesan error (awalnya tersembunyi). Diisi JavaScript bila ada masalah. --}}
                <p id="error" class="hidden mt-3 font-mono text-xs text-brand"></p>
            </section>
    
            {{-- ===== Panel hasil: tabel + grafik ===== --}}
            <section class="mt-8 border border-ink bg-paper p-6 sm:p-10">
                <div class="flex flex-wrap justify-between items-baseline gap-2">
                    <h2 class="font-bold text-xl">Hasil Perbandingan</h2>
    
                    {{-- Info P0 dan r. Saat halaman pertama dibuka, diisi langsung oleh PHP
                         dari wilayah default (Kota Bandung). Setelah benchmark dijalankan,
                         teks ini ditimpa JavaScript sesuai wilayah yang dipilih. --}}
                    <p id="info" class="font-mono text-[10px] text-gray-500">
                        P₀ = {{ number_format($default['populasi'], 0, ',', '.') }} ·
                        r = {{ number_format($default['laju'], 2, ',', '.') }}% /thn
                        ({{ $default['nama'] }}, BPS 2026)
                    </p>
                </div>
    
                <div class="mt-6 grid gap-8 lg:grid-cols-2">
                    {{-- Tabel: kerangkanya statis, isi barisnya (#rows) diisi renderTable() --}}
                    <div class="overflow-x-auto">
                        <table class="w-full font-mono text-[11px]">
                            <thead>
                                <tr class="text-left border-b-2 border-ink">
                                    <th class="py-2 font-bold">t (input)</th>
                                    <th class="font-bold">Iteratif (ms)</th>
                                    <th class="font-bold">Rekursif (ms)</th>
                                    <th class="font-bold">Selisih</th>
                                </tr>
                            </thead>
                            <tbody id="rows"></tbody>
                        </table>
                        {{-- Keterangan di bawah tabel: jumlah pengulangan & pemeriksaan kebenaran hasil --}}
                        <p id="meta" class="mt-3 font-mono text-[10px] text-gray-500"></p>
                    </div>
    
                    {{-- Grafik: elemen SVG kosong, digambar oleh renderChart().
                         viewBox = sistem koordinat internal 420x290, otomatis menyesuaikan lebar layar. --}}
                    <div class="border border-gray-400 bg-canvas p-2">
                        <svg id="chart" viewBox="0 0 420 290" class="w-full"></svg>
                    </div>
                </div>
    
                {{-- Legenda warna + kompleksitas teoritis tiap algoritma --}}
                <p class="mt-6 font-mono text-[11px] flex gap-6">
                    <span><span class="inline-block w-2.5 h-2.5 bg-iter mr-1"></span>Iteratif — O(t)</span>
                    <span><span class="inline-block w-2.5 h-2.5 bg-rec mr-1"></span>Rekursif — O(log t)</span>
                </p>
            </section>
        </div>
    
        {{-- @push('scripts') menaruh JavaScript ini di @stack('scripts') pada layout (sebelum </body>) --}}
        @push('scripts')
            <script>
                // Daftar ukuran input standar. Dipakai untuk mengisi titik-titik pada grafik.
                const PRESETS = [1, 10, 100, 1000, 5000, 10000];
    
                // Singkatan untuk document.getElementById
                const $ = id => document.getElementById(id);
    
                // fmt : angka waktu -> 4 desimal dengan koma (0.0036 -> "0,0036")
                // fmtT: angka t -> format Indonesia dengan titik ribuan (10000 -> "10.000")
                const fmt = n => n.toFixed(4).replace('.', ',');
                const fmtT = n => n.toLocaleString('id-ID');
    
                // ---------- Pengalih mode input (preset <-> manual) ----------
                let manual = false; // false = pakai dropdown preset, true = pakai kolom ketik
    
                function setMode(m) {
                    manual = m;
                    // Tampilkan salah satu, sembunyikan yang lain
                    $('size-select').classList.toggle('hidden', m);
                    $('size-input').classList.toggle('hidden', !m);
                    // Tombol yang aktif berwarna gelap, yang tidak aktif abu-abu
                    $('mode-preset').className = 'px-4 py-2 ' + (m ? 'bg-gray-300' : 'bg-ink text-white');
                    $('mode-manual').className = 'px-4 py-2 ' + (m ? 'bg-ink text-white' : 'bg-gray-300');
                }
                $('mode-preset').onclick = () => setMode(false);
                $('mode-manual').onclick = () => setMode(true);
    
                // ---------- Menggambar tabel ----------
                // rows = array hasil dari server, satu elemen per ukuran t:
                //   { t, iteratif, rekursif, proyeksi, sama }   (waktu dalam milidetik)
                // Nilai null berarti algoritma itu tidak dicentang -> ditampilkan "—".
                function renderTable(rows) {
                    $('rows').innerHTML = rows.map(d => `
                    <tr class="border-b border-gray-300">
                        <td class="py-2.5">${fmtT(d.t)}</td>
                        <td class="font-bold text-iter">${d.iteratif == null ? '—' : fmt(d.iteratif)}</td>
                        <td class="font-bold text-rec">${d.rekursif == null ? '—' : fmt(d.rekursif)}</td>
                        <td class="text-gray-500">${d.iteratif == null || d.rekursif == null ? '—' : fmt(Math.abs(d.iteratif - d.rekursif)) + ' ms'}</td>
                    </tr>`).join('');
                }
    
                // ---------- Menggambar grafik (SVG buatan sendiri, tanpa library) ----------
                function renderChart(rows) {
                    // W,H = lebar/tinggi kanvas; L,Rt,T,Bt = margin kiri, kanan, atas, bawah
                    const W = 420, H = 290, L = 48, Rt = 22, T = 14, Bt = 40;
    
                    // Sumbu X berskala LOGARITMIK: posisi titik = log10(t).
                    // Dipakai karena t berjarak jauh (1 sampai 10.000); skala linear akan
                    // menumpuk titik-titik kecil di kiri.
                    const xMax = Math.max(...rows.map(d => Math.log10(d.t)), 1);
    
                    // Sumbu Y berskala LINEAR, tinggi maksimum = waktu terbesar x 1,15 (ruang kosong di atas)
                    const vals = rows.flatMap(d => [d.iteratif, d.rekursif]).filter(v => v != null);
                    const yMax = Math.max(...vals, 0.0001) * 1.15;
    
                    // Fungsi konversi: nilai data -> koordinat piksel di SVG
                    const X = t => L + (Math.log10(t) / xMax) * (W - L - Rt);
                    const Y = v => H - Bt - (v / yMax) * (H - T - Bt); // sumbu Y SVG terbalik (0 di atas)
    
                    let s = '';
    
                    // Garis bantu horizontal + angka sumbu Y (6 garis: 0, 1/5, ..., 5/5 dari yMax)
                    for (let k = 0; k <= 5; k++) {
                        const v = yMax * k / 5, y = Y(v);
                        s += `<line x1="${L}" x2="${W - Rt}" y1="${y}" y2="${y}" stroke="#c9cec6"/>
                          <text x="${L - 6}" y="${y + 3}" font-size="9" text-anchor="end" fill="#666" font-family="monospace">${v.toFixed(3)}</text>`;
                    }
    
                    // Garis bantu vertikal + label t di sumbu X, satu per ukuran input
                    rows.forEach(d => {
                        s += `<line y1="${T}" y2="${H - Bt}" x1="${X(d.t)}" x2="${X(d.t)}" stroke="#c9cec6"/>
                          <text x="${X(d.t)}" y="${H - Bt + 14}" font-size="9" text-anchor="middle" fill="#666" font-family="monospace">${fmtT(d.t)}</text>`;
                    });
    
                    // Menggambar satu garis data: polyline (penghubung) + lingkaran di tiap titik.
                    // key = 'iteratif' atau 'rekursif'. Kalau algoritmanya tidak dipilih, tidak digambar.
                    const line = (key, color) => {
                        const pts = rows.filter(d => d[key] != null);
                        if (!pts.length) return '';
                        return `<polyline fill="none" stroke="${color}" stroke-width="2" points="${pts.map(d => X(d.t) + ',' + Y(d[key])).join(' ')}"/>` +
                            pts.map(d => `<circle cx="${X(d.t)}" cy="${Y(d[key])}" r="3" fill="${color}"/>`).join('');
                    };
                    s += line('iteratif', '#3a6f96') + line('rekursif', '#d4892a');
    
                    // Judul sumbu
                    s += `<text x="${(L + W - Rt) / 2}" y="${H - 8}" font-size="9" text-anchor="middle" fill="#444" font-family="monospace">t (tahun proyeksi)</text>
                      <text transform="translate(10 ${(T + H - Bt) / 2}) rotate(-90)" font-size="9" text-anchor="middle" fill="#444" font-family="monospace">waktu (ms)</text>`;
    
                    $('chart').innerHTML = s; // pasang hasil gambar ke elemen <svg>
                }
    
                // ---------- Fungsi utama: dijalankan saat tombol diklik ----------
                // async/await = menunggu balasan server tanpa membekukan halaman.
                async function run() {
                    const err = $('error'), btn = $('run');
                    err.classList.add('hidden'); // bersihkan pesan error lama
    
                    // Validasi sisi browser: minimal satu algoritma harus dicentang
                    if (!$('chk-iter').checked && !$('chk-rec').checked) {
                        err.textContent = 'Pilih minimal satu algoritma.';
                        err.classList.remove('hidden');
                        return;
                    }
    
                    // t terbesar yang dipilih pengguna (manual dibatasi 1..10.000)
                    const max = manual
                        ? Math.min(10000, Math.max(1, parseInt($('size-input').value) || 1))
                        : parseInt($('size-select').value);
    
                    // Daftar ukuran yang diuji = semua preset yang <= max, ditambah max itu sendiri.
                    // Set membuang duplikat; sort mengurutkan naik.
                    // Contoh: max = 700 -> [1, 10, 100, 700]
                    const sizes = [...new Set([...PRESETS.filter(n => n <= max), max])].sort((a, b) => a - b);
    
                    // Matikan tombol selama menghitung agar tidak diklik berulang
                    btn.disabled = true; btn.textContent = 'Menghitung…';
                    try {
                        // Kirim permintaan POST ke route 'benchmark.run' (BenchmarkController@run).
                        // X-CSRF-TOKEN = tanda pengaman Laravel; tanpa ini POST ditolak (error 419).
                        const res = await fetch("{{ route('benchmark.run') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            },
                            body: JSON.stringify({
                                wilayah: $('wilayah').value,   // nama wilayah -> server cari P0 & r dari CSV
                                sizes,                         // daftar t yang diuji
                                iteratif: $('chk-iter').checked,
                                rekursif: $('chk-rec').checked,
                            }),
                        });
                        const data = await res.json();
    
                        // Status bukan 2xx (mis. 422 gagal validasi) -> lempar error ke blok catch
                        if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan di server.');
    
                        // Perbarui teks P0 dan r sesuai wilayah yang baru dipilih
                        const w = data.wilayah;
                        $('info').textContent = `P₀ = ${fmtT(w.populasi)} · r = ${w.laju.toString().replace('.', ',')}% /thn (${w.nama}, BPS 2026)`;
    
                        // Keterangan metode + pemeriksaan kebenaran:
                        // r.sama = true bila hasil hitung kedua algoritma identik (lihat PopulationProjection)
                        $('meta').textContent = `Rata-rata dari ${fmtT(data.reps)} kali eksekusi per ukuran input, diukur di server (PHP).`
                            + (data.rows.every(r => r.sama) ? ' Hasil kedua algoritma identik ✓' : ' Peringatan: hasil kedua algoritma berbeda!');
    
                        renderTable(data.rows);
                        renderChart(data.rows);
                    } catch (e) {
                        err.textContent = e.message;
                        err.classList.remove('hidden');
                    } finally {
                        // finally selalu jalan, berhasil atau gagal: kembalikan tombol seperti semula
                        btn.disabled = false; btn.textContent = 'Jalankan Benchmark';
                    }
                }
    
                $('run').onclick = run; // sambungkan tombol ke fungsi run()
                run(); // jalankan sekali saat halaman dibuka supaya tabel & grafik langsung terisi
            </script>
        @endpush
    </x-layouts.app>