<?php

namespace App\Filament\Widgets;

use App\Helpers\Constans;
use App\Models\Applications;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Lang;

class GTMRecentApplications extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'أحدث الطلبات المقدمة';

    public static function canView(): bool
    {
        // Visible to GTM (8) and Admin (1)
        return in_array(Auth::user()->role, [Constans::ROLE_GTM, Constans::ROLE_ADMIN]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applications::query()
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('trainee.full_name')
                    ->label('اسم المتدرب'),
                Tables\Columns\TextColumn::make('administrative.title')
                    ->label('مكان التدريب'),
                Tables\Columns\TextColumn::make('department.title')
                    ->label('التخصص'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state) => Lang::get('translation.status.' . $state, [], 'ar'))
                    ->badge()
                    ->color(fn (string $state): string => match ((int)$state) {
                        Constans::STATUS_NEW => 'warning',
                        Constans::STATUS_STRATED_TRAINING => 'success',
                        Constans::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime()
                    ->since(),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
