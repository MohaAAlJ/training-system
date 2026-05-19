<x-filament-widgets::widget class="fi-wi-table">
    <x-filament::section>
        <x-slot name="heading">{{ $this->getWidgetHeading() }}</x-slot>
        <x-slot name="description">{{ $this->getWidgetDescription() }}</x-slot>

        {{ $this->form }}

        @if ($this->isReady())
            <div style="display: flex; gap: 12px;" class="mt-4 flex flex-nowrap gap-3 overflow-x-auto pb-1">

                {{-- Total --}}
                <div style="display: flex; gap: 7px;" class="flex min-w-max shrink-0 items-center gap-x-3 rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 dark:border-primary-800 dark:bg-primary-900/20">
                    <x-filament::icon icon="heroicon-o-users" class="h-5 w-5 shrink-0 text-primary-600 dark:text-primary-400" />
                    <span class="whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                        إجمالي الطلبات:
                        <strong class="text-primary-600 dark:text-primary-400">{{ $this->getFormattedTotalCount() }}</strong>
                    </span>
                </div>

                {{-- University (hidden for MOH) --}}
                @if ($this->showUniversityCount())
                    <div style="display: flex; gap: 7px;" class="flex min-w-max shrink-0 items-center gap-x-3 rounded-lg border border-info-200 bg-info-50 px-4 py-3 dark:border-info-800 dark:bg-info-900/20">
                        <x-filament::icon icon="heroicon-o-academic-cap" class="h-5 w-5 shrink-0 text-info-600 dark:text-info-400" />
                        <span class="whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            تدريب جامعي:
                            <strong class="text-info-600 dark:text-info-400">{{ $this->getFormattedUniversityCount() }}</strong>
                        </span>
                    </div>
                @endif

                {{-- Practice (hidden for College) --}}
                @if ($this->showPracticeCount())
                    <div style="display: flex; gap: 7px;" class="flex min-w-max shrink-0 items-center gap-x-3 rounded-lg border border-warning-200 bg-warning-50 px-4 py-3 dark:border-warning-800 dark:bg-warning-900/20">
                        <x-filament::icon icon="heroicon-o-briefcase" class="h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400" />
                        <span class="whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            تدريب امتياز:
                            <strong class="text-warning-600 dark:text-warning-400">{{ $this->getFormattedPracticeCount() }}</strong>
                        </span>
                    </div>
                @endif

                {{-- Cancelled & Dropped — only shown when no status is selected --}}
                @unless ($this->hasStatus())
                    <div style="display: flex; gap: 7px;" class="flex min-w-max shrink-0 items-center gap-x-3 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 dark:border-danger-800 dark:bg-danger-900/20">
                        <x-filament::icon icon="heroicon-o-x-circle" class="h-5 w-5 shrink-0 text-danger-600 dark:text-danger-400" />
                        <span class="whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            ملغى:
                            <strong class="text-danger-600 dark:text-danger-400">{{ $this->getFormattedCancelledCount() }}</strong>
                        </span>
                    </div>

                @endunless

            </div>
        @endif
    </x-filament::section>

    {{ $this->table }}
</x-filament-widgets::widget>
