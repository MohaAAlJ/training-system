@php
    $appName = $appName ?? config('app.name');
@endphp
<header class="max-w-6xl mx-auto p-6 flex items-center justify-between">
    <div class="text-lg font-semibold">{{ $appName }}</div>
    <nav class="space-x-3 text-sm">
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="px-3 py-1 rounded bg-gray-800 text-white">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="px-3 py-1 rounded border">Masuk</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="px-3 py-1 rounded border">Daftar</a>
                @endif
            @endauth
        @endif
    </nav>
</header>
