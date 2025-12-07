<aside class="hidden md:flex flex-col w-64 bg-white dark:bg-gray-900 border-r dark:border-gray-800 p-6 gap-6 sticky top-0 h-screen">
    <div class="flex items-center gap-2 mb-8">
        <x-heroicon-o-light-bulb class="h-8 w-8 text-blue-500" />
        <span class="font-extrabold text-xl tracking-wider">{{ __('app.name') }}</span>
    </div>
    <nav class="flex flex-col gap-2">
        <a href="/" class="flex items-center gap-3 px-4 py-2 rounded-xl bg-blue-50 dark:bg-gray-800 text-blue-600 font-semibold transition duration-150">
            <x-heroicon-o-home class="h-5 w-5" />
            {{ __('sidebar.dashboard') }}
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition duration-150">
              <x-heroicon-o-academic-cap class="h-5 w-5" />
              {{ __('sidebar.institution') }}
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition duration-150">
              <x-heroicon-o-eye class="h-5 w-5" />
              {{ __('sidebar.major') }}
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition duration-150">
              <x-heroicon-o-briefcase class="h-5 w-5" />
              {{ __('sidebar.department') }}
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition duration-150">
              <x-heroicon-o-arrow-down class="h-5 w-5" />
              {{ __('sidebar.applications') }}
        </a>
    </nav>
    <div class="mt-auto">
        <button type="button" onclick="document.getElementById('settings-modal').classList.remove('hidden')" class="w-full flex items-center gap-3 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 transition duration-150">
              <x-heroicon-o-cog class="h-5 w-5" />
              {{ __('sidebar.switch_theme') }}
        </button>
    </div>
</aside>

<!-- Settings Modal -->
<div id="settings-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm hidden">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-6 w-full max-w-sm relative">
        <button type="button" onclick="document.getElementById('settings-modal').classList.add('hidden')" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            <x-heroicon-o-x-mark class="h-6 w-6" />
        </button>
        <h2 class="text-lg font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
            <x-heroicon-o-cog class="h-5 w-5 text-blue-500" />
            {{ __('modal.settings') }}
        </h2>
        <div class="space-y-4">
            <!-- Theme Switch -->
            <div class="flex items-center justify-between">
                <span class="text-gray-700 dark:text-gray-200">{{ __('modal.theme') }}</span>
                <button type="button" onclick="document.documentElement.classList.toggle('dark')" class="inline-flex items-center gap-2 px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700">
                    <x-heroicon-o-moon class="h-5 w-5" />
                    <span class="text-xs">{{ __('modal.dark_light') }}</span>
                </button>
            </div>
            <!-- Language Switch -->
            <div class="flex items-center justify-between">
                <span class="text-gray-700 dark:text-gray-200">{{ __('modal.language') }}</span>
                <form method="POST" action="{{ route('set-locale') }}" class="flex gap-2">
                    @csrf
                    <select name="locale" class="rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 px-2 py-1">
                        <option value="id" @if(app()->getLocale() == 'id') selected @endif>{{ __('modal.indonesian') }}</option>
                        <option value="en" @if(app()->getLocale() == 'en') selected @endif>{{ __('modal.english') }}</option>
                        <option value="ar" @if(app()->getLocale() == 'ar') selected @endif>{{ __('modal.arabic') }}</option>
                    </select>
                    <button type="submit" class="px-2 py-1 rounded bg-blue-600 text-white text-xs">{{ __('modal.ok') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
