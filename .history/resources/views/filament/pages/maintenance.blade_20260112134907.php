<x-filament-panels::page>
    <div class="fixed inset-0 min-h-screen flex items-center justify-center overflow-hidden z-50 maintenance-bg">
        {{-- Background Effects --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[70vw] h-[70vw] rounded-full bg-primary-500/20 blur-[100px] animate-blob"></div>
            <div class="absolute -bottom-[20%] -right-[10%] w-[70vw] h-[70vw] rounded-full bg-blue-500/20 blur-[100px] animate-blob animation-delay-2000"></div>
            <div class="absolute top-[20%] left-[20%] w-[50vw] h-[50vw] rounded-full bg-purple-500/20 blur-[100px] animate-blob animation-delay-4000"></div>
        </div>

        <div class="relative w-full max-w-lg mx-4">
            {{-- Main Card --}}
            <div class="relative bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/20 dark:border-gray-700/50 transform transition-all hover:scale-[1.01] duration-500">
                
                {{-- Decorative Top Bar --}}
                <div class="h-2 w-full bg-gradient-to-r from-primary-500 via-purple-500 to-blue-500"></div>

                <div class="p-8 sm:p-12 text-center">
                    {{-- Animated Icon --}}
                    <div class="mb-8 relative inline-block">
                        <div class="absolute inset-0 bg-primary-500/20 rounded-full blur-xl animate-pulse"></div>
                        <div class="relative w-24 h-24 bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-full flex items-center justify-center shadow-lg border border-gray-200/50 dark:border-gray-700/50 mx-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-primary-600 dark:text-primary-400 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Content --}}
                    <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 via-gray-700 to-gray-900 dark:from-white dark:via-gray-200 dark:to-white mb-4 tracking-tight">
                        النظام تحت الصيانة
                    </h1>

                    <div class="prose dark:prose-invert mx-auto mb-8">
                        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
                            {{ $this->maintenanceMessage }}
                        </p>
                    </div>

                    {{-- Action Button --}}
                    <div class="transform hover:scale-105 transition-transform duration-300">
                        <x-filament::button 
                            tag="a" 
                            :href="route('filament.Home.auth.logout')" 
                            color="danger" 
                            size="xl"
                            class="w-full sm:w-auto shadow-lg shadow-red-500/30 font-bold px-8 py-3 rounded-xl"
                        >
                            <span class="flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                تسجيل الخروج
                            </span>
                        </x-filament::button>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50/50 dark:bg-gray-800/50 p-4 text-center border-t border-gray-100 dark:border-gray-700">
                    <p class="text-sm text-gray-400 font-medium">
                        نظام التدريب &copy; {{ date('Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Force fullscreen override for Filament Simple Layout */
        .fi-simple-layout, .fi-body {
            background: transparent !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .maintenance-bg {
            background-color: #0f172a; /* Slate 900 */
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(168, 85, 247, 0.1) 0px, transparent 50%);
        }

        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        .animate-spin-slow {
            animation: spin 8s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</x-filament-panels::page>