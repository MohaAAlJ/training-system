<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages;
use App\Filament\Resources\Users\Schemas\UsersForm; // استدعاء الفورم الخاص بك
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Constans; // تأكد من استدعاء ملف الثوابت

class UsersResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'المستخدمين';

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        // استدعاء الفورم الذي قمت أنت ببرمجته
        return UsersForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('الدور')
                    ->badge()
                    ->formatStateUsing(fn($state) => Constans::ROLE_LABELS[$state] ?? $state),
                Tables\Columns\IconColumn::make('status')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->filters([
                // 1. فلتر المحذوفات (Soft Delete)
                TrashedFilter::make()
                    ->visible(fn() => Auth::user()->isAdmin()), // يظهر للأدمن فقط

                // 2. فلتر حسب الدور
                SelectFilter::make('role')
                    ->label('تصفية حسب الدور')
                    ->options(Constans::ROLE_LABELS),

                // 3. فلتر حسب الحالة
                SelectFilter::make('status')
                    ->label('تصفية حسب الحالة')
                    ->options([
                        1 => 'نشط',
                        0 => 'غير نشط',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // زر الحذف العادي (للأدمن فقط)
                DeleteAction::make()
                    ->visible(fn() => Auth::user()->isAdmin()),

                // زر الحذف النهائي (للأدمن فقط)
                ForceDeleteAction::make()
                    ->visible(fn() => Auth::user()->isAdmin()),

                // زر الاستعادة (في حال كان محذوفاً)
                RestoreAction::make()
                    ->visible(fn() => Auth::user()->isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->isAdmin()),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->isAdmin()),
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn() => Auth::user()->isAdmin()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUsers::route('/create'),
            'edit' => Pages\EditUsers::route('/{record}/edit'),
        ];
    }

    // تفعيل Soft Deletes في الاستعلام
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
