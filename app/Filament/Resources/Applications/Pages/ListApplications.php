<?php

namespace App\Filament\Resources\Applications\Pages;

// use App\Enums\ApplicationStatus;
// use App\Enums\TrainingType;
use App\Filament\Pages\Concerns\InteractsWithAiTableSearch;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;

class ListApplications extends ListRecords
{
    use InteractsWithAiTableSearch;

    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة طلب')
                ->visible(static fn() => Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry())),

            ApplicationsTable::getImportExcelAction()
                ->label('إضافة طلبات'),
        ];
    }

    protected function getAiSearchResourceKey(): string
    {
        return 'applications';
    }

    protected function getAiSearchHeading(): string
    {
        return 'البحث الذكي في الطلبات';
    }

    protected function getAiSearchDescription(): string
    {
        return 'حوّل الوصف الحر إلى فلاتر آمنة داخل التبويب الحالي فقط.';
    }

    protected function getAiSearchPromptLabel(): string
    {
        return 'صف الطلبات التي تريد الوصول إليها';
    }

    protected function getAiSearchPlaceholder(): string
    {
        return 'مثال: طلبات التمريض من جامعة القدس في قائمة الانتظار هذا الشهر';
    }

    protected function getAiSearchScopeLabel(): ?string
    {
        return match ($this->activeTab) {
            'new' => 'ضمن تبويب: ' . Application::getStatusLabel(Application::STATUS_NEW),
            'initial_approve' => 'ضمن تبويب: ' . (Auth::user()?->isTrainingManagerLike()
                ? 'في انتظار التأكيد'
                : Application::getStatusLabel(Application::STATUS_INITIAL_APPROVE)),
            'confirmed' => 'ضمن تبويب: ' . Application::getStatusLabel(Application::STATUS_CONFIRMATION),
            'training' => 'ضمن تبويب: ' . Application::getStatusLabel(Application::STATUS_STARTED_TRAINING),
            'finished' => 'ضمن تبويب: ' . Application::getStatusLabel(Application::STATUS_ENDED_TRAINING),
            'cancelled' => 'ضمن تبويب: ' . Application::getStatusLabel(Application::STATUS_CANCELLED),
            'rejected' => 'ضمن تبويب: ' . Application::getStatusLabel(Application::STATUS_REJECTED),
            'waiting_list' => 'ضمن تبويب: ' . Application::getStatusLabel(Application::STATUS_WAITING_LIST),
            'all' => 'ضمن تبويب: الكل ما عدا المرفوضة',
            default => 'ضمن تبويب الطلبات الحالي',
        };
    }

    protected function getAiSearchExamples(): array
    {
        return [
            'المتدربين في قائمة الانتظار بقسم المختبر',
            'practice trainees from nursing this month',
            'طلبات جامعة القدس بتخصص تمريض',
        ];
    }

    protected function getAiSearchContext(): array
    {
        return [
            'active_tab' => $this->activeTab,
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

    public function getTabs(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $tabs = [];
        $baseQuery = static::getResource()::getEloquentQuery();

        // Role-specific Status Tabs

        // NEW (Admin/GTM only)
        if ($user->isAdmin() || $user->isTrainingManagerLike()) {
            $tabs['new'] = Tab::make(Application::getStatusLabel(Application::STATUS_NEW))
                ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_NEW))
                ->badge($baseQuery->clone()->where('status', Application::STATUS_NEW)->count());
        }

        // INITIAL_APPROVE (Admin/GTM/MOH/College)
        if ($user->isAdmin() || $user->isTrainingManagerLike() || $user->isMinistry() || $user->isCollegeSupervisor()) {
            $label = ($user->isTrainingManagerLike()) ? 'في انتظار التأكيد' : Application::getStatusLabel(Application::STATUS_INITIAL_APPROVE);

            $tabs['initial_approve'] = Tab::make($label)
                ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_INITIAL_APPROVE))
                ->badge($baseQuery->clone()->where('status', Application::STATUS_INITIAL_APPROVE)->count());
        }

        // CONFIRMATION (Admin/GTM/MOH/College)
        if ($user->isAdmin() || $user->isTrainingManagerLike() || $user->isMinistry() || $user->isCollegeSupervisor()) {
            $tabs['confirmed'] = Tab::make(Application::getStatusLabel(Application::STATUS_CONFIRMATION))
                ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_CONFIRMATION))
                ->badge($baseQuery->clone()->where('status', Application::STATUS_CONFIRMATION)->count());
        }


        // TRAINING (Everyone)
        $tabs['training'] = Tab::make(Application::getStatusLabel(Application::STATUS_STARTED_TRAINING))
            ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_STARTED_TRAINING))
            ->badge($baseQuery->clone()->where('status', Application::STATUS_STARTED_TRAINING)->count());

        // FINISHED (Everyone)
        $tabs['finished'] = Tab::make(Application::getStatusLabel(Application::STATUS_ENDED_TRAINING))
            ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_ENDED_TRAINING))
            ->badge($baseQuery->clone()->where('status', Application::STATUS_ENDED_TRAINING)->count());

        // CANCELLED (Admin/GTM/College/MOH)
        if ($user->isAdmin() || $user->isTrainingManagerLike() || $user->isCollegeSupervisor() || $user->isMinistry()) {
            $tabs['cancelled'] = Tab::make(Application::getStatusLabel(Application::STATUS_CANCELLED))
                ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_CANCELLED))
                ->badge($baseQuery->clone()->where('status', Application::STATUS_CANCELLED)->count());
        }

        // REJECTED (Admin/GTM only)
        if ($user->isAdmin() || $user->isTrainingManagerLike()) {
            $tabs['rejected'] = Tab::make(Application::getStatusLabel(Application::STATUS_REJECTED))
                ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_REJECTED))
                ->badge($baseQuery->clone()->where('status', Application::STATUS_REJECTED)->count());

            // Training Type Tabs (Admin/GTM only as they see both types)
            // $tabs['separator'] = Tab::make('---')
            //     ->modifyQueryUsing(fn($query) => $query)
            //     ->badge(null);

            // $tabs['university_training'] = Tab::make(Application::getTrainingTypeLabel(Application::UNIVERSITY))
            //     ->modifyQueryUsing(fn($query) => $query->where('training_type', Application::UNIVERSITY))
            //     ->badge($baseQuery->clone()->where('training_type', Application::UNIVERSITY)->count());

            // $tabs['practice_training'] = Tab::make(Application::getTrainingTypeLabel(Application::PRACTICE))
            //     ->modifyQueryUsing(fn($query) => $query->where('training_type', Application::PRACTICE))
            //     ->badge($baseQuery->clone()->where('training_type', Application::PRACTICE)->count());
        }
 // WAITING_LIST (Everyone has access)
        $tabs['waiting_list'] = Tab::make(Application::getStatusLabel(Application::STATUS_WAITING_LIST))
            ->modifyQueryUsing(fn($query) => $query->where('status', Application::STATUS_WAITING_LIST))
            ->badge($baseQuery->clone()->where('status', Application::STATUS_WAITING_LIST)->count());

        // "All" Tab — placed last
        $tabs['all'] = Tab::make('الكل')
            ->modifyQueryUsing(fn($query) => $query->where('status', '!=', Application::STATUS_REJECTED))
            ->badge($baseQuery->clone()->where('status', '!=', Application::STATUS_REJECTED)->count());

        return $tabs;
    }
}
