<?php

namespace App\Filament\Widgets;

use App\Helpers\Constants;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

class AdminLatestUsers extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public static ?string $heading = 'سجل أحدث المستخدمين المسجلين';


    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return '';
    }

    public static function canView(): bool
    {
        return !request()->routeIs('filament.home.pages.dashboard') && Auth::user()->role === User::ROLE_ADMIN;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query(
                User::query()
                    ->active()
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
                    ->formatStateUsing(fn($state) => User::ROLE_LABELS[$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->date('d/m/Y'),

            ])
            ->recordUrl(
                fn (User $record): string => \App\Filament\Resources\Users\UserResource::getUrl('view', ['record' => $record]),
            );
    }
}
