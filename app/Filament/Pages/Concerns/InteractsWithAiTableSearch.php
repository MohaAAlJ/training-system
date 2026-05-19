<?php

namespace App\Filament\Pages\Concerns;

use App\Services\AiSearch\AiTableSearchInterpreter;
use App\Support\AiSearch\AiTableSearchFeature;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\View as ViewComponent;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithAiTableSearch
{
    public string $aiSearchPrompt = '';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $aiSearchFilters = null;

    public ?string $aiSearchSummary = null;

    public ?string $aiSearchKeyword = null;

    abstract protected function getAiSearchResourceKey(): string;

    abstract protected function getAiSearchHeading(): string;

    abstract protected function getAiSearchDescription(): string;

    /**
     * @return array<int, string>
     */
    abstract protected function getAiSearchExamples(): array;

    protected function getAiSearchPromptLabel(): string
    {
        return 'صف ما تريد العثور عليه';
    }

    protected function getAiSearchPlaceholder(): string
    {
        return 'اكتب طلب البحث بلغة طبيعية...';
    }

    protected function getAiSearchSubmitLabel(): string
    {
        return 'بحث ذكي';
    }

    protected function getAiSearchScopeLabel(): ?string
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAiSearchContext(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        $table = parent::table($table);

        return $table->modifyQueryUsing(function (Builder $query): void {
            if (! $this->hasActiveAiSearch() || ! $this->canUseAiTableSearch()) {
                return;
            }

            app(AiTableSearchInterpreter::class)->applyToQuery(
                resource: $this->getAiSearchResourceKey(),
                query: $query,
                filters: $this->aiSearchFilters ?? [],
                keyword: $this->aiSearchKeyword,
                user: auth()->user(),
            );
        });
    }

    public function applyAiSearch(): void
    {
        if (! $this->canUseAiTableSearch()) {
            Notification::make()
                ->title('البحث الذكي غير متاح لهذا المستخدم.')
                ->danger()
                ->send();

            return;
        }

        $result = app(AiTableSearchInterpreter::class)->interpret(
            resource: $this->getAiSearchResourceKey(),
            rawQuery: $this->aiSearchPrompt,
            user: auth()->user(),
            context: $this->getAiSearchContext(),
        );

        if (! $result->success) {
            Notification::make()
                ->title($result->error ?? 'تعذر تفسير طلب البحث.')
                ->danger()
                ->send();

            return;
        }

        $this->aiSearchFilters = $result->filters;
        $this->aiSearchSummary = $result->summary;
        $this->aiSearchKeyword = $result->keyword;

        $this->resetPage();
    }

    public function clearAiSearch(): void
    {
        $this->aiSearchPrompt = '';
        $this->aiSearchFilters = null;
        $this->aiSearchSummary = null;
        $this->aiSearchKeyword = null;

        $this->resetPage();
    }

    protected function hasActiveAiSearch(): bool
    {
        return filled($this->aiSearchKeyword) || filled($this->aiSearchFilters);
    }

    protected function canUseAiTableSearch(): bool
    {
        return app(AiTableSearchFeature::class)->enabledFor(auth()->user());
    }

    /**
     * @return array<int, ViewComponent>
     */
    protected function getAiSearchContentComponents(): array
    {
        if (! $this->canUseAiTableSearch()) {
            return [];
        }

        return [
            ViewComponent::make('filament.components.ai-table-search-panel')
                ->viewData([
                    'heading' => $this->getAiSearchHeading(),
                    'description' => $this->getAiSearchDescription(),
                    'examples' => $this->getAiSearchExamples(),
                    'promptLabel' => $this->getAiSearchPromptLabel(),
                    'placeholder' => $this->getAiSearchPlaceholder(),
                    'submitLabel' => $this->getAiSearchSubmitLabel(),
                    'scopeLabel' => $this->getAiSearchScopeLabel(),
                    'summary' => $this->aiSearchSummary,
                ]),
        ];
    }
}
