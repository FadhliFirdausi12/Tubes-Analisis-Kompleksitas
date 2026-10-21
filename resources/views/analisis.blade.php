<x-layouts.app title="Analisis">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

        <p class="flex items-center gap-2 font-mono text-xs text-gray-600">
            <span class="w-3 h-3 bg-brand inline-block"></span> Benchmark
        </p>
        <h1 class="text-4xl font-bold tracking-tight mt-3">Jalankan Benchmark</h1>
        <p class="font-mono text-xs leading-5 text-gray-700 mt-3 max-w-md">
            Pilih wilayah, ukuran input, dan algoritma yang ingin diuji. Iteratif dan rekursif dijalankan
            pada ukuran input yang sama persis agar hasilnya bisa dibandingkan secara adil.
        </p>

        {{-- Parameter --}}
        <section class="mt-8 border border-ink bg-paper p-5">
            <h2 class="font-bold text-lg">Parameter Pengujian</h2>

            <div class="mt-4 flex flex-wrap items-end gap-x-10 gap-y-5">

                <div>
                    <p class="text-xs mb-2">Wilayah</p>
                    <select id="wilayah" class="w-44 border border-ink bg-gray-200 px-3 py-2 font-mono text-sm">
                        @foreach ($wilayah as $w)
                            <option value="{{ $w['nama'] }}">{{ $w['nama'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <p class="text-xs mb-2">Metode Ukuran Input</p>
                    <div class="flex border border-ink font-mono text-xs">
                        <button id="mode-preset" class="px-4 py-2 bg-ink text-white">Dropdown Preset</button>
                        <button id="mode-manual" class="px-4 py-2 bg-gray-300">Input Manual</button>
                    </div>
                </div>

                <div>
                    <p class="text-xs mb-2">Ukuran Input (t)</p>
                    <select id="size-select" class="w-40 border border-ink bg-gray-200 px-3 py-2 font-mono text-sm">
                        @foreach ([1, 10, 100, 1000, 5000, 10000] as $n)
                            <option value="{{ $n }}" @selected($n === 10000)>{{ number_format($n, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <input id="size-input" type="number" min="1" max="10000" value="10000"
                        class="hidden w-40 border border-ink bg-gray-200 px-3 py-2 font-mono text-sm">
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
                    <p class="text-xs mb-3">t = tahun proyeksi (maks. 10.000)</p>
                    <button id="run"
                        class="bg-brand hover:opacity-90 disabled:opacity-50 text-white font-mono px-8 py-2.5">
                        Jalankan Benchmark
                    </button>
                </div>
            </div>
            <p id="error" class="hidden mt-3 font-mono text-xs text-brand"></p>
        </section>

        {{-- Hasil --}}
        <section class="mt-8 border border-ink bg-paper p-6 sm:p-10">
            <div class="flex flex-wrap justify-between items-baseline gap-2">
                <h2 class="font-bold text-xl">Hasil Perbandingan</h2>
                <p id="info" class="font-mono text-[10px] text-gray-500">
                    P₀ = {{ number_format($default['populasi'], 0, ',', '.') }} ·
                    r = {{ number_format($default['laju'], 2, ',', '.') }}% /thn
                    ({{ $default['nama'] }}, BPS 2026)
                </p>
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
                    <p id="meta" class="mt-3 font-mono text-[10px] text-gray-500"></p>
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
            const PRESETS = [1, 10, 100, 1000, 5000, 10000];
            const $ = id => document.getElementById(id);
            const fmt = n => n.toFixed(4).replace('.', ',');
            const fmtT = n => n.toLocaleString('id-ID');

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

            function renderTable(rows) {
                $('rows').innerHTML = rows.map(d => `
                <tr class="border-b border-gray-300">
                    <td class="py-2.5">${fmtT(d.t)}</td>
                    <td class="font-bold text-iter">${d.iteratif == null ? '—' : fmt(d.iteratif)}</td>
                    <td class="font-bold text-rec">${d.rekursif == null ? '—' : fmt(d.rekursif)}</td>
                    <td class="text-gray-500">${d.iteratif == null || d.rekursif == null ? '—' : fmt(Math.abs(d.iteratif - d.rekursif)) + ' ms'}</td>
                </tr>`).join('');
            }

            function renderChart(rows) {
                const W = 420, H = 290, L = 48, Rt = 22, T = 14, Bt = 40;
                const xMax = Math.max(...rows.map(d => Math.log10(d.t)), 1);
                const vals = rows.flatMap(d => [d.iteratif, d.rekursif]).filter(v => v != null);
                const yMax = Math.max(...vals, 0.0001) * 1.15;
                const X = t => L + (Math.log10(t) / xMax) * (W - L - Rt);
                const Y = v => H - Bt - (v / yMax) * (H - T - Bt);

                let s = '';
                for (let k = 0; k <= 5; k++) {
                    const v = yMax * k / 5, y = Y(v);
                    s += `<line x1="${L}" x2="${W - Rt}" y1="${y}" y2="${y}" stroke="#c9cec6"/>
                      <text x="${L - 6}" y="${y + 3}" font-size="9" text-anchor="end" fill="#666" font-family="monospace">${v.toFixed(3)}</text>`;
                }
                rows.forEach(d => {
                    s += `<line y1="${T}" y2="${H - Bt}" x1="${X(d.t)}" x2="${X(d.t)}" stroke="#c9cec6"/>
                      <text x="${X(d.t)}" y="${H - Bt + 14}" font-size="9" text-anchor="middle" fill="#666" font-family="monospace">${fmtT(d.t)}</text>`;
                });
                const line = (key, color) => {
                    const pts = rows.filter(d => d[key] != null);
                    if (!pts.length) return '';
                    return `<polyline fill="none" stroke="${color}" stroke-width="2" points="${pts.map(d => X(d.t) + ',' + Y(d[key])).join(' ')}"/>` +
                        pts.map(d => `<circle cx="${X(d.t)}" cy="${Y(d[key])}" r="3" fill="${color}"/>`).join('');
                };
                s += line('iteratif', '#3a6f96') + line('rekursif', '#d4892a');
                s += `<text x="${(L + W - Rt) / 2}" y="${H - 8}" font-size="9" text-anchor="middle" fill="#444" font-family="monospace">t (tahun proyeksi)</text>
                  <text transform="translate(10 ${(T + H - Bt) / 2}) rotate(-90)" font-size="9" text-anchor="middle" fill="#444" font-family="monospace">waktu (ms)</text>`;
                $('chart').innerHTML = s;
            }

            async function run() {
                const err = $('error'), btn = $('run');
                err.classList.add('hidden');

                if (!$('chk-iter').checked && !$('chk-rec').checked) {
                    err.textContent = 'Pilih minimal satu algoritma.';
                    err.classList.remove('hidden');
                    return;
                }

                const max = manual
                    ? Math.min(10000, Math.max(1, parseInt($('size-input').value) || 1))
                    : parseInt($('size-select').value);
                const sizes = [...new Set([...PRESETS.filter(n => n <= max), max])].sort((a, b) => a - b);

                btn.disabled = true; btn.textContent = 'Menghitung…';
                try {
                    const res = await fetch("{{ route('benchmark.run') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        },
                        body: JSON.stringify({
                            wilayah: $('wilayah').value,
                            sizes,
                            iteratif: $('chk-iter').checked,
                            rekursif: $('chk-rec').checked,
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan di server.');

                    const w = data.wilayah;
                    $('info').textContent = `P₀ = ${fmtT(w.populasi)} · r = ${w.laju.toString().replace('.', ',')}% /thn (${w.nama}, BPS 2026)`;
                    $('meta').textContent = `Rata-rata dari ${fmtT(data.reps)} kali eksekusi per ukuran input, diukur di server (PHP).`
                        + (data.rows.every(r => r.sama) ? ' Hasil kedua algoritma identik ✓' : ' Peringatan: hasil kedua algoritma berbeda!');
                    renderTable(data.rows);
                    renderChart(data.rows);
                } catch (e) {
                    err.textContent = e.message;
                    err.classList.remove('hidden');
                } finally {
                    btn.disabled = false; btn.textContent = 'Jalankan Benchmark';
                }
            }

            $('run').onclick = run;
            run(); // hasil awal saat halaman dibuka
        </script>
    @endpush
</x-layouts.app>