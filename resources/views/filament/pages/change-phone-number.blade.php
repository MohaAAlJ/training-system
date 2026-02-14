<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <form wire:submit="save">
            {{ $this->form }}

            <div class="flex justify-end gap-3 mt-6">
                <x-filament::button type="submit" size="lg" icon="heroicon-o-check-circle">
                    حفظ التغييرات
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>