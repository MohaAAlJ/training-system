<?php

namespace App\Filament\Resources\Trainees\Pages;

use App\Filament\Pages\Concerns\InteractsWithAiTableSearch;
use App\Filament\Resources\Trainees\TraineeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;

class ListTrainees extends ListRecords
{
    use InteractsWithAiTableSearch;

    protected static string $resource = TraineeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getAiSearchResourceKey(): string
    {
        return 'trainees';
    }

    protected function getAiSearchHeading(): string
    {
        return 'البحث الذكي في المتدربين';
    }

    protected function getAiSearchDescription(): string
    {
        return 'ابحث بالاسم أو الهوية أو من خلال سياق الطلبات المرتبطة بالمتدرب.';
    }

    protected function getAiSearchPromptLabel(): string
    {
        return 'صف المتدربين الذين تريد العثور عليهم';
    }

    protected function getAiSearchPlaceholder(): string
    {
        return 'مثال: متدربات من رام الله قيد التدريب في الإدارة الطبية';
    }

    protected function getAiSearchScopeLabel(): ?string
    {
        return 'ضمن سجل المتدربين';
    }

    protected function getAiSearchExamples(): array
    {
        return [
            'المتدربين بقائمة الانتظار في قسم المختبر',
            'female trainees from Ramallah',
            'المتدربين قيد التدريب في الإدارة الطبية',
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getTabsContentComponent(),
            ...$this->getAiSearchContentComponents(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
        ]);
    }
}
