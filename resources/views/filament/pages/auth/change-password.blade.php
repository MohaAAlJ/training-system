<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6 max-w-xl">
        {{ $this->form }}

        {{-- Password Requirements --}}
        <div class="fi-fo-field-wrp rounded-lg border border-gray-200 dark:border-white/10 p-4">
            <p class="text-sm font-medium text-gray-950 dark:text-white mb-3">متطلبات كلمة المرور:</p>
            <div class="text-sm">
                <span style="color: {{ $hasMinLength ? '#16a34a' : '#dc2626' }}; margin-left: 4.5rem;">
                    {{ $hasMinLength ? '✓' : '✗' }} 8+ أحرف
                </span>
                <span style="color: {{ $hasLetters ? '#16a34a' : '#dc2626' }}; margin-left: 4.5rem;">
                    {{ $hasLetters ? '✓' : '✗' }} أحرف
                </span>
                <span style="color: {{ $hasNumbers ? '#16a34a' : '#dc2626' }}; margin-left: 4.5rem;">
                    {{ $hasNumbers ? '✓' : '✗' }} أرقام
                </span>
                <span style="color: {{ $hasSymbols ? '#16a34a' : '#dc2626' }}; margin-left: 4.5rem;">
                    {{ $hasSymbols ? '✓' : '✗' }} رموز
                </span>
                <span style="color: {{ $hasMixedCase ? '#16a34a' : '#dc2626' }}; margin-left: 4.5rem;">
                    {{ $hasMixedCase ? '✓' : '✗' }} كبيرة/صغيرة
                </span>
            </div>
        </div>

        <x-filament::button type="submit" :disabled="!$this->allRulesPass()">
            حفظ التغييرات
        </x-filament::button>
    </form>
</x-filament-panels::page>
