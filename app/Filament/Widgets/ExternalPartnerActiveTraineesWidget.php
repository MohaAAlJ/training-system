<?php

namespace App\Filament\Widgets;

use App\Helpers\Constans;
use App\Models\Administrative;
use App\Models\Applications;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ExternalPartnerActiveTraineesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'المتدربين الحاليين حسب أماكن التدريب';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        return in_array($user->role, [
            Constans::ROLE_MOH,
            Constans::ROLE_COLLEGE,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Administrative::query()
                    ->withCount(['applications as active_trainees_count' => function ($query) {
                        $user = Auth::user();
                        $query->where('status', Constans::STATUS_STRATED_TRAINING);

                        if ($user->isCollegeSupervisor()) {
                            $collegeId = $user->college?->id;
                            $query->where('training_type', Constans::TRAINING_TYPE_UNIVERSITY)
                                ->whereHas('trainee', function ($q) use ($collegeId) {
                                    $q->where('college_id', $collegeId);
                                });
                        }

                        if ($user->isMinistry()) {
                            $query->where('training_type', Constans::TRAINING_TYPE_PRACTICE);
                        }
                    }])
                    ->having('active_trainees_count', '>', 0)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('مكان التدريب'),
                Tables\Columns\TextColumn::make('active_trainees_count')
                    ->label('عدد المتدربين الحاليين')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
