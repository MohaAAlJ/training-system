<?php

namespace App\Filament\Widgets;

use App\Models\User;

use App\Helpers\Constants;
use App\Models\Administrative;
use App\Models\Application;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ExternalPartnerActiveTraineesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    public static ?string $heading = 'المتدربين الحاليين حسب أماكن التدريب';

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return '';
    }

    public static function canView(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query(
                Administrative::query()
                    ->active()
                    ->withCount(['applications as active_Trainee_count' => function ($query) {
                        $user = Auth::user();
                        $query->where('applications.status', Application::STATUS_STARTED_TRAINING);

                        if ($user->isCollegeSupervisor()) {
                            $collegeId = $user->college?->id;
                            $query->where('applications.training_type', Application::UNIVERSITY)
                                ->whereHas('trainee', function ($q) use ($collegeId) {
                                    $q->where('college_id', $collegeId);
                                });
                        }

                        if ($user->isMinistry()) {
                            $query->where('applications.training_type', Application::PRACTICE);
                        }
                    }])
                    ->having('active_Trainee_count', '>', 0)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('مكان التدريب'),
                Tables\Columns\TextColumn::make('active_Trainee_count')
                    ->label('عدد المتدربين الحاليين')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->recordUrl(
                fn (Administrative $record): string => \App\Filament\Resources\Administratives\AdministrativeResource::getUrl('view', ['record' => $record]),
            );
    }
}
