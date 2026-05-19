<?php

namespace App\Filament\Widgets;

use App\Models\Administrative;
use App\Models\Application;
use App\Models\User;
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
                    ->withCount(['applications as active_trainee_count' => function ($query) {
                        $user = Auth::user();
                        $query->where('applications.status', Application::STATUS_STARTED_TRAINING);

                        if ($user->isCollegeSupervisor()) {
                            $college = $user->college;
                            if (!$college || $user->college()->active()->doesntExist()) {
                                $query->whereRaw('0 = 1');
                                return;
                            }
                            $collegeId = $college->id;
                            $query->where('applications.training_type', Application::UNIVERSITY)
                                ->where('applications.college_id', $collegeId);
                        }

                        if ($user->isMinistry()) {
                            if ($user->mohDepartment && $user->mohDepartment()->active()->doesntExist()) {
                                $query->whereRaw('0 = 1');
                                return;
                            }
                            $query->where('applications.training_type', Application::PRACTICE);
                            // Filter by MOH's department if connected
                            if ($user->mohDepartment) {
                                $query->whereHas('section', fn($q) => $q->whereHas('departments', fn($dq) => $dq->where('departments.id', $user->mohDepartment->id)->visible()));
                            }
                        }
                    }])
                    ->having('active_trainee_count', '>', 0)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('مكان التدريب'),
                Tables\Columns\TextColumn::make('active_trainee_count')
                    ->label('عدد المتدربين الحاليين')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->recordUrl(
                fn(Administrative $record): string => \App\Filament\Resources\Administratives\AdministrativeResource::getUrl('view', ['record' => $record]),
            );
    }
}
