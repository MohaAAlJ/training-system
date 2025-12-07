@php
    // Inisialisasi variabel sudah OK, tapi kita bisa buat lebih rapi dengan view composers jika mau
    $appName = config('app.name');
    $counts = $counts ?? [];
    $recentTrainees = $recentTrainees ?? collect();
    $topMajors = $topMajors ?? collect();
    $latestApplications = $latestApplications ?? collect();
@endphp

@extends('layouts.app')

@section('content')
<div class="flex flex-col md:flex-row min-h-[80vh] bg-gray-50 dark:bg-gray-900">

    @include('components.sidebar', ['appName' => $appName])

    <main class="flex-1 p-4 md:p-8">

        <section class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h1 class="text-3xl font-extrabold flex items-center gap-2 text-gray-900 dark:text-white">
                    <x-heroicon-o-light-bulb class="h-8 w-8 text-blue-500" />
                    {{ __('dashboard.welcome', ['name' => 'Lycus']) }}
                </h1>
                <div class="flex gap-3">
                    <a href="#" class="inline-flex items-center gap-1 px-5 py-2.5 bg-blue-600 text-white rounded-xl font-medium shadow-md hover:bg-blue-700 transition duration-150">
                        <x-heroicon-o-plus class="h-5 w-5" />
                        {{ __('dashboard.add_trainee') }}
                    </a>
                    <a href="#" class="inline-flex items-center gap-1 px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-medium shadow-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition duration-150">
                        <x-heroicon-o-document class="h-5 w-5" />
                        {{ __('dashboard.export_data') }}
                    </a>
                </div>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ __('dashboard.description') }}</p>
        </section>

        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
            <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl flex items-center gap-4 shadow-lg border border-gray-100 dark:border-gray-700">
                <span class="bg-blue-100 text-blue-600 rounded-full p-3 flex-shrink-0">
                    <x-heroicon-o-academic-cap class="h-6 w-6" />
                </span>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ __('dashboard.total_institutions') }}</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $counts['institutions'] ?? 0 }}</div>
                </div>
            </div>
            <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl flex items-center gap-4 shadow-lg border border-gray-100 dark:border-gray-700">
                <span class="bg-green-100 text-green-600 rounded-full p-3 flex-shrink-0">
                    <x-heroicon-o-eye class="h-6 w-6" />
                </span>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ __('dashboard.total_majors') }}</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $counts['majors'] ?? 0 }}</div>
                </div>
            </div>
            <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl flex items-center gap-4 shadow-lg border border-gray-100 dark:border-gray-700">
                <span class="bg-yellow-100 text-yellow-600 rounded-full p-3 flex-shrink-0">
                    <x-heroicon-o-briefcase class="h-6 w-6" />
                </span>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ __('dashboard.total_departments') }}</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $counts['departments'] ?? 0 }}</div>
                </div>
            </div>
            <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl flex items-center gap-4 shadow-lg border border-gray-100 dark:border-gray-700">
                <span class="bg-pink-100 text-pink-600 rounded-full p-3 flex-shrink-0">
                    <x-heroicon-o-arrow-down class="h-6 w-6" />
                </span>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ __('dashboard.applications_pending') }}</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $counts['applications_pending'] ?? 0 }}</div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">

            <section class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-100 dark:border-gray-700">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2 flex items-center gap-2 text-gray-800 dark:text-white">
                    <x-heroicon-o-bell class="h-6 w-6 text-blue-500" />
                    {{ __('dashboard.recent_trainees') }}
                </h2>
                <ul class="space-y-3">
                    @forelse($recentTrainees as $t)
                        <li class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg transition duration-150 hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $t->full_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ optional($t->institution)->name ?? '—' }} | {{ optional($t->major)->name ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 flex-shrink-0">{{ $t->dob?->format('d M Y') ?? '' }}</div>
                        </li>
                    @empty
                        <li class="p-3 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg">{{ __('dashboard.no_recent_trainees') }}</li>
                    @endforelse
                </ul>
            </section>

            <section class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-100 dark:border-gray-700">
                <h3 class="text-xl font-semibold mb-4 border-b pb-2 flex items-center gap-2 text-gray-800 dark:text-white">
                    <x-heroicon-o-chart-bar class="h-6 w-6 text-green-500" />
                    {{ __('dashboard.top_majors') }}
                </h3>
                <ul class="space-y-3 text-sm">
                    @forelse($topMajors as $m)
                        <li class="flex justify-between items-center py-2 border-b dark:border-gray-700 last:border-b-0">
                            <span class="text-gray-700 dark:text-gray-300">{{ $m->name }}</span>
                            <span class="text-sm font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-600 dark:bg-gray-700 dark:text-green-400">{{ $m->trainees_count }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500 dark:text-gray-400">{{ __('dashboard.no_top_majors') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <section class="mt-4 md:mt-6 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-100 dark:border-gray-700">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2 flex items-center gap-2 text-gray-800 dark:text-white">
                <x-heroicon-o-arrow-down class="h-6 w-6 text-pink-500" />
                {{ __('dashboard.latest_applications') }}
            </h2>
            <ul class="space-y-3">
                @forelse($latestApplications as $app)
                    <li class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg flex items-center justify-between transition duration-150 hover:bg-gray-100 dark:hover:bg-gray-600">
                        <div class="flex items-center gap-3">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ optional($app->trainee)->full_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ optional($app->department)->name_location ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 flex-shrink-0">{{ $app->start_date?->format('d M Y') ?? '' }}</div>
                    </li>
                @empty
                    <li class="p-3 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg">{{ __('dashboard.no_latest_applications') }}</li>
                @endforelse
            </ul>
        </section>

    </main>
</div>
@endsection
