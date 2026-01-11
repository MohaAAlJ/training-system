<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}
        
        <div class="flex items-center gap-4">
            {{ $this->getSaveFormAction() }}
        </div>
    </form>
</x-filament-panels::page>
