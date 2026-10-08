@php
    // Atur menu di sini saja. Desktop & mobile otomatis ikut.
    $links = [
        ['no' => '01', 'label' => 'Dashboard',         'url' => '#',                   'active' => false],
        ['no' => '02', 'label' => 'Analisis',          'url' => route('analisis'),     'active' => request()->routeIs('analisis')],
        ['no' => '03', 'label' => 'Tentang Algoritma', 'url' => '#',                   'active' => false],
    ];
@endphp

<header class="sticky top-0 z-50 bg-paper border-t-4 border-iter">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-[68px] flex items-center justify-between">

        <a href="{{ route('analisis') }}" class="flex items-center gap-3">
            <span class="grid place-items-center w-10 h-10 rounded-sm bg-brand text-white font-serif text-lg">P↑</span>
            <span class="text-2xl font-bold tracking-tight">PowRace</span>
            <span class="hidden lg:block ml-2 font-mono text-[11px] uppercase tracking-wider text-gray-500">
                Proyeksi Penduduk .BPS
            </span>
        </a>

        {{-- Desktop --}}
        <nav class="hidden md:flex items-center gap-8 font-mono text-xs">
            @foreach ($links as $l)
                <a href="{{ $l['url'] }}"
                   class="pb-1 border-b-2 {{ $l['active'] ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-ink' }}">
                    <span class="text-rec">{{ $l['no'] }}</span> {{ $l['label'] }}
                </a>
            @endforeach
        </nav>

        <button id="nav-toggle" class="md:hidden p-2" aria-label="Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    {{-- Mobile --}}
    <nav id="nav-menu" class="hidden md:hidden px-4 pb-3 font-mono text-sm space-y-1">
        @foreach ($links as $l)
            <a href="{{ $l['url'] }}" class="block px-3 py-2 {{ $l['active'] ? 'bg-brand text-white' : 'hover:bg-canvas' }}">
                {{ $l['no'] }} {{ $l['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="stripe"></div>

    <script>
        document.getElementById('nav-toggle').addEventListener('click', () =>
            document.getElementById('nav-menu').classList.toggle('hidden'));
    </script>
</header>