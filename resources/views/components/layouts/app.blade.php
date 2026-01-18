<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'نظام التدريب' }}</title>

    <!-- Early theme initialization to prevent flash -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('form-assets/trainee-app/styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('form-assets/welcome/styles.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body style="margin: 0; padding: 0;">
    {{ $slot }}

    <!-- Livewire Scripts -->
    @livewireScripts
</body>

</html>