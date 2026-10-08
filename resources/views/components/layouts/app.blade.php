@props(['title' => 'PowRace'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} · PowRace</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|space-mono:400,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-canvas text-ink font-sans antialiased">

    {{-- Navbar: components/navbar.blade.php --}}
    <x-navbar />

    {{-- Isi halaman --}}
    <main class="flex-1 w-full">
        {{ $slot }}
    </main>

    {{-- Footer: components/footer.blade.php --}}
    <x-footer />

    @stack('scripts')
</body>
</html> 