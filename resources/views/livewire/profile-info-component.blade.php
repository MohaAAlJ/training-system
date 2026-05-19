<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

    {{-- Header --}}
    <header class="fi-section-header flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-white/10">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 ring-1 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20">
            <span style="width:1rem;height:1rem;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                <x-heroicon-o-identification style="width:1rem;height:1rem;display:block;" />
            </span>
        </div>
        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">معلومات الحساب</h3>
    </header>

    {{-- Rows --}}
    <dl class="divide-y divide-gray-100 dark:divide-white/10">

        {{-- Username --}}
        <div class="flex items-center justify-between px-6 py-3.5">
            <dt class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <span style="width:0.875rem;height:0.875rem;flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    <x-heroicon-m-user style="width:0.875rem;height:0.875rem;display:block;" />
                </span>
                اسم المستخدم
            </dt>
            <dd class="text-sm font-medium text-gray-950 dark:text-white">
                {{ $user->user_name ?? $user->name }}
            </dd>
        </div>

        {{-- Email --}}
        <div class="flex items-center justify-between px-6 py-3.5">
            <dt class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <span style="width:0.875rem;height:0.875rem;flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    <x-heroicon-m-envelope style="width:0.875rem;height:0.875rem;display:block;" />
                </span>
                البريد الإلكتروني
            </dt>
            <dd class="text-sm font-medium text-gray-950 dark:text-white" dir="ltr">
                {{ $user->email }}
            </dd>
        </div>

        {{-- Phone --}}
        <div class="px-6 py-3.5">

            {{-- View state --}}
            @if (!$confirming && !$editing)
                <div class="flex items-center justify-between">
                    <dt class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <span style="width:0.875rem;height:0.875rem;flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                            <x-heroicon-m-phone style="width:0.875rem;height:0.875rem;display:block;" />
                        </span>
                        رقم الهاتف
                    </dt>
                    <dd class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-950 dark:text-white" dir="ltr">
                            {{ $user->phone_number ?? '—' }}
                        </span>
                        <button
                            wire:click="startChange"
                            type="button"
                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium
                                ring-1 ring-inset ring-gray-300 text-gray-600 bg-white hover:bg-gray-50
                                dark:ring-white/15 dark:text-gray-300 dark:bg-white/5 dark:hover:bg-white/10
                                transition-colors duration-150"
                        >
                            <span style="width:0.875rem;height:0.875rem;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                <x-heroicon-m-pencil-square style="width:0.875rem;height:0.875rem;display:block;" />
                            </span>
                            تغيير
                        </button>
                    </dd>
                </div>
            @endif

            {{-- Confirm step --}}
            @if ($confirming)
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-400/20 dark:bg-amber-400/10">
                    <div class="flex items-start gap-3">
                        <span style="width:1.25rem;height:1.25rem;flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center;margin-top:0.125rem;color:rgb(217 119 6);">
                            <x-heroicon-o-exclamation-triangle style="width:1.25rem;height:1.25rem;display:block;" />
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">تأكيد تغيير رقم الهاتف</p>
                            <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-400">سيتم تحديث رقم الهاتف المرتبط بحسابك.</p>
                            <div class="mt-3 flex items-center gap-2">
                                <button wire:click="confirmChange" type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white
                                        hover:bg-primary-500 transition-colors dark:bg-primary-500 dark:hover:bg-primary-400">
                                    <span style="width:0.875rem;height:0.875rem;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                        <x-heroicon-m-check style="width:0.875rem;height:0.875rem;display:block;" />
                                    </span>
                                    تأكيد
                                </button>
                                <button wire:click="cancelChange" type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold
                                        ring-1 ring-inset ring-gray-300 text-gray-700 bg-white hover:bg-gray-50
                                        dark:ring-white/20 dark:text-gray-300 dark:bg-transparent dark:hover:bg-white/5 transition-colors">
                                    <span style="width:0.875rem;height:0.875rem;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                        <x-heroicon-m-x-mark style="width:0.875rem;height:0.875rem;display:block;" />
                                    </span>
                                    إلغاء
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Edit step --}}
            @if ($editing)
                <div>
                    <label for="newPhone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        رقم الهاتف الجديد
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            id="newPhone"
                            wire:model="newPhone"
                            type="tel"
                            dir="ltr"
                            placeholder="07xxxxxxxx"
                            class="fi-input block w-full max-w-xs rounded-lg border-0 py-2 px-3 text-sm text-gray-950 shadow-sm
                                ring-1 ring-inset ring-gray-300 placeholder:text-gray-400
                                focus:ring-2 focus:ring-primary-600
                                dark:bg-white/5 dark:text-white dark:ring-white/15 dark:placeholder:text-gray-500 dark:focus:ring-primary-500"
                        />
                        <button
                            wire:click="savePhone"
                            wire:loading.attr="disabled"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-xs font-semibold text-white
                                hover:bg-primary-500 disabled:opacity-60 transition-colors dark:bg-primary-500 dark:hover:bg-primary-400">
                            <span wire:loading wire:target="savePhone">
                                <svg style="width:0.875rem;height:0.875rem;display:block;" class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="savePhone" style="width:0.875rem;height:0.875rem;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                <x-heroicon-m-check style="width:0.875rem;height:0.875rem;display:block;" />
                            </span>
                            حفظ
                        </button>
                        <button wire:click="cancelChange" type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold
                                ring-1 ring-inset ring-gray-300 text-gray-700 bg-white hover:bg-gray-50
                                dark:ring-white/20 dark:text-gray-300 dark:bg-transparent dark:hover:bg-white/5 transition-colors">
                            <span style="width:0.875rem;height:0.875rem;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                <x-heroicon-m-x-mark style="width:0.875rem;height:0.875rem;display:block;" />
                            </span>
                            إلغاء
                        </button>
                    </div>
                    @error('newPhone')
                        <p class="mt-2 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Success toast --}}
            @if ($successMessage)
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 4000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mt-2 flex items-center gap-1.5 text-xs font-medium text-success-600 dark:text-success-400"
                >
                    <span style="width:1rem;height:1rem;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                        <x-heroicon-m-check-circle style="width:1rem;height:1rem;display:block;" />
                    </span>
                    {{ $successMessage }}
                </div>
            @endif

        </div>
    </dl>

</div>
