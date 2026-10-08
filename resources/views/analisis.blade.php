<x-layouts.app title="Analisis">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

        {{-- Header halaman --}}
        <p class="flex items-center gap-2 font-mono text-xs text-gray-600">
            <span class="w-3 h-3 bg-brand inline-block"></span> Benchmark
        </p>
        <h1 class="text-4xl font-bold tracking-tight mt-3">Jalankan Benchmark</h1>
        <p class="font-mono text-xs leading-5 text-gray-700 mt-3 max-w-md">
            Pilih ukuran input dan algoritma yang ingin diuji. Iteratif dan rekursif dijalankan
            pada ukuran input yang sama persis agar hasilnya bisa dibandingkan secara adil.
        </p>

        {{-- Parameter --}}
        <section class="mt-8 border border-ink bg-paper p-5">
            <h2 class="font-bold text-lg">Parameter Pengujian</h2>

            <div class="mt-4 flex flex-wrap items-end gap-x-10 gap-y-5">

                <div>
                    <p class="text-xs mb-2">Metode Ukuran Input</p>
                    <div class="flex border border-ink font-mono text-xs">
                        <button id="mode-preset" class="px-4 py-2 bg-ink text-white">Dropdown Preset</button>
                        <button id="mode-manual" class="px-4 py-2 bg-gray-300">Input Manual</button>
                    </div>
                </div>

                <div>
                    <p class="text-xs mb-2">Ukuran Input (t)</p>
                    <select id="size-select" class="w-48 border border-ink bg-gray-200 px-3 py-2 font-mono text-sm">
                        @foreach ([1, 10, 100, 1000, 5000, 10000] as $n)
                            <option value="{{ $n }}" @selected($n === 10000)>
                                {{ number_format($n, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    <input id="size-input" type="number" min="1" max="100000" value="10000"
                        class="hidden w-48 border border-ink bg-gray-200 px-3 py-2 font-mono text-sm">
                </div>

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

                <div class="ml-auto text-right">
                    <p class="text-xs mb-3">t = tahun proyeksi</p>
                    <button id="run" class="bg-brand hover:opacity-90 text-white font-mono px-8 py-2.5">
                        Jalankan Benchmark
                    </button>
                </div>
            </div>
        </section>

        {{-- Hasil --}}
        <section class="mt-8 border border-ink bg-paper p-6 sm:p-10">
            <div class="flex flex-wrap justify-between items-baseline gap-2">
                <h2 class="font-bold text-xl">Hasil Perbandingan</h2>
                <p class="font-mono text-[10px] text-gray-500">P₀ = 2.543.900 · r = 1,14% /thn (Kota Bandung, BPS)</p>
            </div>

            <div class="mt-6 grid gap-8 lg:grid-cols-2">
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
                </div>

                <div class="border border-gray-400 bg-canvas p-2">
                    <svg id="chart" viewBox="0 0 420 290" class="w-full"></svg>
                </div>
            </div>

            <p class="mt-6 font-mono text-[11px] flex gap-6">
                <span><span class="inline-block w-2.5 h-2.5 bg-iter mr-1"></span>Iteratif — O(t)</span>
                <span><span class="inline-block w-2.5 h-2.5 bg-rec mr-1"></span>Rekursif — O(log t)</span>
            </p>
        </section>
    </div>

    @push('scripts')
        <script>
            const P0 = 2543900,
                R = 0.0114,
                PRESETS = [1, 10, 100, 1000, 5000, 10000];
            const $ = id => document.getElementById(id);

            // Dua algoritma pangkat: (1 + r)^t
            const iter = t => {
                let x = 1;
                for (let i = 0; i < t; i++) x *= 1 + R;
                return x;
            };
            const rec = (b, t) => t === 0 ? 1 : (t % 2 ? b * rec(b, t - 1) : rec(b * b, t / 2));

            function time(fn, reps = 200) {
                const s = performance.now();
                for (let i = 0; i < reps; i++) fn();
                return (performance.now() - s) / reps;
            }

            // Ganti mode input
            let manual = false;

            function setMode(m) {
                manual = m;
                $('size-select').classList.toggle('hidden', m);
                $('size-input').classList.toggle('hidden', !m);
                $('mode-preset').className = 'px-4 py-2 ' + (m ? 'bg-gray-300' : 'bg-ink text-white');
                $('mode-manual').className = 'px-4 py-2 ' + (m ? 'bg-ink text-white' : 'bg-gray-300');
            }
            $('mode-preset').onclick = () => setMode(false);
            $('mode-manual').onclick = () => setMode(true);

            const fmt = n => n.toFixed(3).replace('.', ',');
            const fmtT = n => n.toLocaleString('id-ID');

            function renderTable(data) {
                $('rows').innerHTML = data.map(d => `
                <tr class="border-b border-gray-300">
                    <td class="py-2.5">${fmtT(d.t)}</td>
                    <td class="font-bold text-iter">${d.i == null ? '—' : fmt(d.i)}</td>
                    <td class="font-bold text-rec">${d.r == null ? '—' : fmt(d.r)}</td>
                    <td class="text-gray-500">${d.i == null || d.r == null ? '—' : fmt(Math.abs(d.i - d.r)) + ' ms'}</td>
                </tr>`).join('');
            }

            function renderChart(data) {
                const W = 420,
                    H = 290,
                    L = 36,
                    Rt = 14,
                    T = 14,
                    Bt = 40;
                const xs = data.map(d => Math.log10(d.t)),
                    xMax = Math.max(...xs, 1);
                const vals = data.flatMap(d => [d.i, d.r]).filter(v => v != null);
                const yMax = Math.max(...vals, 0.001) * 1.15;
                const X = t => L + (Math.log10(t) / xMax) * (W - L - Rt);
                const Y = v => H - Bt - (v / yMax) * (H - T - Bt);

                let s = '';
                for (let k = 0; k <= 5; k++) {
                    const v = yMax * k / 5,
                        y = Y(v);
                    s +=
                        `<line x1="${L}" x2="${W - Rt}" y1="${y}" y2="${y}" stroke="#c9cec6"/>
                    <text x="${L - 6}" y="${y + 3}" font-size="9" text-anchor="end" fill="#666" font-family="monospace">${v.toFixed(2)}</text>`;
                }
                data.forEach(d => {
                    s +=
                        `<line y1="${T}" y2="${H - Bt}" x1="${X(d.t)}" x2="${X(d.t)}" stroke="#c9cec6"/>
                    <text x="${X(d.t)}" y="${H - Bt + 14}" font-size="9" text-anchor="middle" fill="#666" font-family="monospace">${fmtT(d.t)}</text>`;
                });
                const line = (key, color) => {
                    const pts = data.filter(d => d[key] != null);
                    if (!pts.length) return '';
                    return `<polyline fill="none" stroke="${color}" stroke-width="2" points="${pts.map(d => X(d.t) + ',' + Y(d[key])).join(' ')}"/>` +
                        pts.map(d => `<circle cx="${X(d.t)}" cy="${Y(d[key])}" r="3" fill="${color}"/>`).join('');
                };
                s += line('i', '#3a6f96') + line('r', '#d4892a');
                s +=
                    `<text x="${(L + W - Rt) / 2}" y="${H - 8}" font-size="9" text-anchor="middle" fill="#444" font-family="monospace">t (tahun proyeksi)</text>
                <text transform="translate(10 ${(T + H - Bt) / 2}) rotate(-90)" font-size="9" text-anchor="middle" fill="#444" font-family="monospace">waktu (ms)</text>`;
                $('chart').innerHTML = s;
            }

            function run() {
                const max = manual ? Math.max(1, parseInt($('size-input').value) || 1) : parseInt($('size-select').value);
                const sizes = [...new Set([...PRESETS.filter(n => n <= max), max])].sort((a, b) => a - b);
                const data = sizes.map(t => ({
                    t,
                    i: $('chk-iter').checked ? time(() => P0 * iter(t)) : null,
                    r: $('chk-rec').checked ? time(() => P0 * rec(1 + R, t)) : null,
                }));
                renderTable(data);
                renderChart(data);
            }

            $('run').onclick = run;
            run(); // tampilkan hasil awal saat halaman dibuka
        </script>
    @endpush
</x-layouts.app>