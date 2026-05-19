<style>
    .ai-search-panel {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        padding: 1.5rem;
        border: 2px solid rgba(148, 163, 184, 0.15);
        border-radius: 1.25rem;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .ai-search-panel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.3), transparent);
        pointer-events: none;
    }

    .dark .ai-search-panel {
        border-color: rgba(148, 163, 184, 0.12);
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.25) 0%, rgba(15, 23, 42, 0.12) 100%);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    .ai-search-panel__meta {
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: rgb(148, 163, 184);
        font-weight: 600;
        opacity: 0.8;
    }

    .ai-search-panel__instruction {
        font-size: 0.875rem;
        line-height: 1.6;
        color: rgb(226, 232, 240);
        font-weight: 500;
        padding: 0.875rem 1rem;
        background: rgba(239, 68, 68, 0.05);
        border-radius: 0.875rem;
        border-left: 4px solid rgba(239, 68, 68, 0.3);
    }

    .dark .ai-search-panel__instruction {
        background: rgba(239, 68, 68, 0.08);
        border-left-color: rgba(239, 68, 68, 0.4);
    }

    .ai-search-panel__label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: rgb(248, 250, 252);
        letter-spacing: -0.01em;
        margin-bottom: 0.5rem;
    }

    .ai-search-panel__textarea {
        display: block;
        width: 100%;
        min-height: 7rem;
        max-height: 12rem;
        resize: vertical;
        border-radius: 1rem;
        border: 2px solid rgba(148, 163, 184, 0.2);
        background: rgba(15, 23, 42, 0.35);
        padding: 1rem;
        font-size: 0.95rem;
        line-height: 1.6;
        color: rgb(248, 250, 252);
        transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
        font-family: inherit;
        box-sizing: border-box;
    }

    .ai-search-panel__textarea::placeholder {
        color: rgb(148, 163, 184);
        opacity: 0.7;
    }

    .ai-search-panel__textarea:hover {
        border-color: rgba(148, 163, 184, 0.3);
        background: rgba(15, 23, 42, 0.4);
    }

    .ai-search-panel__textarea:focus {
        outline: none;
        border-color: rgba(239, 68, 68, 0.5);
        background: rgba(15, 23, 42, 0.45);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .ai-search-panel__actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }

    @media (min-width: 640px) {
        .ai-search-panel__actions {
            flex-direction: row;
            gap: 1rem;
            justify-content: flex-start;
            align-items: center;
        }
    }

    .ai-search-panel__submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        flex: 1;
        min-width: 10rem;
        border-radius: 0.875rem;
        background: linear-gradient(135deg, rgb(239, 68, 68) 0%, rgb(220, 38, 38) 100%);
        padding: 0.85rem 1.5rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: white;
        letter-spacing: -0.01em;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
    }

    .ai-search-panel__submit:hover:not(:disabled) {
        background: linear-gradient(135deg, rgb(229, 62, 62) 0%, rgb(210, 32, 32) 100%);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.35);
        transform: translateY(-2px);
    }

    .ai-search-panel__submit:active:not(:disabled) {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
    }

    .ai-search-panel__submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    @media (min-width: 640px) {
        .ai-search-panel__submit {
            flex: 0 0 auto;
            width: auto;
            min-width: 10rem;
        }
    }

    .ai-search-panel__clear {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        flex: 1;
        min-width: 10rem;
        border-radius: 0.875rem;
        border: 2px solid rgba(148, 163, 184, 0.3);
        background: rgba(148, 163, 184, 0.1);
        padding: 0.85rem 1.5rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: rgb(226, 232, 240);
        cursor: pointer;
        transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ai-search-panel__clear:hover:not(:disabled) {
        border-color: rgba(239, 68, 68, 0.5);
        background: rgba(239, 68, 68, 0.15);
        color: rgb(248, 250, 252);
    }

    .ai-search-panel__clear:active:not(:disabled) {
        background: rgba(239, 68, 68, 0.2);
    }

    .ai-search-panel__clear:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    @media (min-width: 640px) {
        .ai-search-panel__clear {
            flex: 0 0 auto;
            width: auto;
            min-width: 10rem;
        }
    }

    .ai-search-panel__examples {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(148, 163, 184, 0.1);
    }

    .ai-search-panel__examples-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: rgb(148, 163, 184);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.9;
    }

    .ai-search-panel__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .ai-search-panel__chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 0.875rem;
        border: 2px solid rgba(148, 163, 184, 0.2);
        background: rgba(148, 163, 184, 0.05);
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: rgb(226, 232, 240);
        cursor: pointer;
        transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
    }

    .ai-search-panel__chip:hover {
        border-color: rgba(239, 68, 68, 0.4);
        background: rgba(239, 68, 68, 0.12);
        color: rgb(248, 250, 252);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
    }

    .ai-search-panel__chip:active {
        transform: translateY(0);
    }

    .ai-search-panel__summary {
        padding: 1rem;
        border-radius: 0.875rem;
        border: 1px solid rgba(239, 68, 68, 0.2);
        background: rgba(239, 68, 68, 0.08);
        font-size: 0.85rem;
        line-height: 1.6;
        color: rgb(226, 232, 240);
    }

    .dark .ai-search-panel__summary {
        background: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.25);
    }

    .ai-search-panel__summary-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        font-weight: 700;
        color: rgb(248, 250, 252);
    }
</style>

<x-filament::section
    :heading="$heading"
    :description="$description"
    icon="heroicon-o-sparkles"
    compact
    collapsible
    collapsed
>
    <div
        class="ai-search-panel"
        x-data="{
            prompt: $wire.entangle('aiSearchPrompt'),
            isLoading: false,
            resize() {
                const el = this.$refs.prompt;
                if (! el) return;
                el.style.height = '0px';
                el.style.height = `${Math.min(el.scrollHeight, 180)}px`;
            },
            init() {
                this.$watch('prompt', () => this.$nextTick(() => this.resize()));
                this.$nextTick(() => this.resize());
                
                // Track loading state
                this.$watch(() => this.$wire.entangle('aiSearchPrompt'), () => {
                    this.isLoading = false;
                });
            }
        }"
        x-init="init()"
    >
        {{-- Scope Label --}}
        @if (filled($scopeLabel))
            <p class="ai-search-panel__meta">
                {{ $scopeLabel }}
            </p>
        @endif

        {{-- Instructions --}}
        <p class="ai-search-panel__instruction">
            اكتب وصفًا قصيرًا وواضحًا، مثل: اسم المتدرب، الحالة، القسم، أو الجامعة.
        </p>

        {{-- Search Form --}}
        <form wire:submit.prevent="applyAiSearch" style="display: flex; flex-direction: column; gap: 0.5rem;">
            {{-- Input Group --}}
            <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                <label for="ai-search-prompt" class="ai-search-panel__label">
                    {{ $promptLabel }}
                </label>

                <textarea
                    id="ai-search-prompt"
                    x-ref="prompt"
                    x-model="prompt"
                    rows="5"
                    dir="auto"
                    class="ai-search-panel__textarea"
                    placeholder="{{ $placeholder }}"
                    x-on:input="resize()"
                    x-on:keydown.ctrl.enter="isLoading = true; $el.closest('form').requestSubmit()"
                    x-on:keydown.meta.enter="isLoading = true; $el.closest('form').requestSubmit()"
                    @wireloading.prevent="isLoading = true"
                    @wireupdating.prevent="isLoading = false"
                ></textarea>
            </div>

            {{-- Action Buttons --}}
            <div class="ai-search-panel__actions">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="applyAiSearch"
                    class="ai-search-panel__submit"
                >
                    <span wire:loading.remove wire:target="applyAiSearch">{{ $submitLabel }}</span>
                    <span wire:loading wire:target="applyAiSearch">جارٍ البحث...</span>
                </button>

                {{-- Clear Button --}}
                <button
                    type="button"
                    wire:click="clearAiSearch"
                    wire:loading.attr="disabled"
                    wire:target="clearAiSearch"
                    class="ai-search-panel__clear"
                >
                    مسح البحث
                </button>
            </div>
        </form>

        {{-- Quick Example Suggestions --}}
        @if (filled($examples))
            <div class="ai-search-panel__examples">
                <p class="ai-search-panel__examples-label">أمثلة سريعة:</p>

                <div class="ai-search-panel__chips">
                    @foreach ($examples as $example)
                        <button
                            type="button"
                            x-on:click="$wire.set('aiSearchPrompt', {{ \Illuminate\Support\Js::from($example) }}); isLoading = false"
                            class="ai-search-panel__chip"
                            title="انقر لتطبيق هذا المثال"
                        >
                            {{ $example }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Active Filters Summary --}}
        @if (filled($summary))
            <div class="ai-search-panel__summary">
                <span class="ai-search-panel__summary-label">الفلاتر الحالية:</span>
                <span>{{ $summary }}</span>
            </div>
        @endif
    </div>
</x-filament::section>
