<?php

namespace App\Filament\Widgets;

use App\Helpers\Constans;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class AdminLatestUsers extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'أحدث المستخدمين المسجلين';

    public static function canView(): bool
    {
        return Auth::user()->role === Constans::ROLE_ADMIN;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم'),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني'),
                Tables\Columns\TextColumn::make('role')
                    ->label('الدور')
                    ->formatStateUsing(fn ($state) => Constans::ROLE_LABELS[$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime(),
            ]);
    }
}
