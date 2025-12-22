<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Institution;
use App\Models\College;
use App\Models\Major;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Actions\Action;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Constans;

class ApplicationsForm
{
    // Helper to decode JSON names
    protected static function getLocalizedName($model)
    {
        if (!$model) return '';
        $name = $model->name;
        if (is_string($name)) {
            $decoded = json_decode($name, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $name = $decoded;
            }
        }

        if (is_array($name)) {
            return $name[app()->getLocale()] ?? $name['ar'] ?? $name['en'] ?? reset($name);
        }
        return $name;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('trainee_id'),

                Fieldset::make('البيانات الشخصية للمتدرب')
                    ->schema([
                        TextInput::make('full_name')
                            ->label('الاسم الكامل')
                            ->formatStateUsing(fn($record) => $record?->trainee?->full_name)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required()
                            ->suffixAction(
                                Action::make('edit_trainee_details')
                                    ->icon('heroicon-m-pencil-square')
                                    ->label('تعديل')
                                    ->visible(fn($context) => $context === 'edit' && (Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor()))
                                    ->modalHeading('تعديل بيانات المتدرب')
                                    ->mountUsing(fn($record, $form) => $form->fill([
                                        'full_name' => $record->trainee->full_name,
                                        'national_id' => $record->trainee->national_id,
                                        'phone_number' => $record->trainee->phone_number,
                                        'dob' => $record->trainee->dob,
                                        'address' => $record->trainee->address,
                                        'institution_id' => $record->trainee->institution_id,
                                        'college_id' => $record->trainee->college_id,
                                        'major_id' => $record->trainee->major_id,
                                    ]))
                                    ->form([
                                        TextInput::make('full_name')->label('الاسم الكامل')->required(),
                                        TextInput::make('national_id')->label('رقم الهوية')->required(),
                                        TextInput::make('phone_number')->label('رقم الهاتف')->required(),
                                        TextInput::make('address')->label('العنوان'),
                                        DatePicker::make('dob')->label('تاريخ الميلاد')->native(false),
                                        Select::make('major_id')
                                            ->label('التخصص')
                                            ->options(Major::all()->mapWithKeys(fn($item) => [$item->id => self::getLocalizedName($item)]))
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->action(function ($record, $data) {
                                        $record->trainee->update($data);
                                        \Filament\Notifications\Notification::make()->title('تم التحديث')->success()->send();
                                    })
                            ),

                        TextInput::make('national_id')
                            ->label('رقم الهوية')
                            ->formatStateUsing(fn($record) => $record?->trainee?->national_id)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(),

                        TextInput::make('phone_number')
                            ->label('رقم الهاتف')
                            ->formatStateUsing(fn($record) => $record?->trainee?->phone_number)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(),

                        TextInput::make('address')
                            ->label('العنوان')
                            ->formatStateUsing(fn($record) => $record?->trainee?->address)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(),

                        DatePicker::make('dob')
                            ->label('تاريخ الميلاد')
                            ->formatStateUsing(fn($record) => $record?->trainee?->dob)
                            ->native(false)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(),

                        Select::make('institution_id')
                            ->label('المؤسسة التعليمية')
                            ->options(function () {
                                if (Auth::user()->isCollegeSupervisor() && Auth::user()->college) {
                                    $inst = Auth::user()->college->institution;
                                    return [$inst->id => self::getLocalizedName($inst)];
                                }
                                return Institution::all()->mapWithKeys(fn($item) => [$item->id => self::getLocalizedName($item)]);
                            })
                            ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->institution_id : null)
                            ->formatStateUsing(fn($record) => $record?->trainee?->institution_id)
                            ->disabled(fn() => Auth::user()->isCollegeSupervisor() || request()->routeIs('*.edit'))
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(fn() => Auth::user()->isAdmin())
                            ->reactive(),

                        Select::make('college_id')
                            ->label('الكلية')
                            ->options(function (callable $get) {
                                if (Auth::user()->isCollegeSupervisor() && Auth::user()->college) {
                                    $col = Auth::user()->college;
                                    return [$col->id => self::getLocalizedName($col)];
                                }

                                $institutionId = $get('institution_id');
                                if ($institutionId) {
                                    return College::where('institution_id', $institutionId)
                                        ->get()
                                        ->mapWithKeys(fn($item) => [$item->id => self::getLocalizedName($item)]);
                                }
                                return [];
                            })
                            ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college_id : null)
                            ->formatStateUsing(fn($record) => $record?->trainee?->college_id)
                            ->disabled(fn() => Auth::user()->isCollegeSupervisor() || request()->routeIs('*.edit'))
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(fn() => Auth::user()->isAdmin())
                            ->reactive(),

                        Select::make('major_id')
                            ->label('التخصص')
                            ->options(function (callable $get) {
                                if (Auth::user()->isCollegeSupervisor() && Auth::user()->college) {
                                    return Major::whereHas('colleges', function ($q) {
                                        $q->where('colleges.id', Auth::user()->college_id);
                                    })->get()->mapWithKeys(fn($item) => [$item->id => self::getLocalizedName($item)]);
                                }
                                $collegeId = $get('college_id');
                                if ($collegeId) {
                                    return Major::whereHas('colleges', fn($q) => $q->where('colleges.id', $collegeId))
                                        ->get()
                                        ->mapWithKeys(fn($item) => [$item->id => self::getLocalizedName($item)]);
                                }
                                return [];
                            })
                            ->formatStateUsing(fn($record) => $record?->trainee?->major_id)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->searchable()
                            ->required()
                            ->preload(),
                    ])->columns(2),

                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                        Select::make('training_type')
                            ->label('نوع التدريب')
                            ->options(Constans::TRAINING_TYPE_LABELS)
                            ->default(function () {
                                if (Auth::user()->isCollegeSupervisor()) return Constans::TRAINING_TYPE_UNIVERSITY;
                                if (Auth::user()->isMinistry()) return Constans::TRAINING_TYPE_PROFESSIONAL;
                                return null;
                            })
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->dehydrated()
                            ->required(),

                        TextInput::make('duration')
                            ->label('مدة التدريب (بالساعات)')
                            ->numeric()
                            ->default(100)
                            ->suffix('ساعة')
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        Select::make('department_id')
                            ->label('الدائرة (التخصص)')
                            ->relationship('department', 'title')
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('administrative_id', null);
                                $set('section_id', null);
                            })
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        Select::make('administrative_id')
                            ->label('الادارة (المكان)')
                            ->options(function (callable $get) {
                                $deptId = $get('department_id');
                                if (! $deptId) return [];
                                return \App\Models\Administratives::whereHas('sections', function ($query) use ($deptId) {
                                    $query->where('department_id', $deptId);
                                })->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn($set) => $set('section_id', null))
                            ->disabled(fn(callable $get) => ! $get('department_id') || (! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor()))
                            ->required(),

                        Select::make('section_id')
                            ->label('القسم')
                            ->options(function (callable $get) {
                                $adminId = $get('administrative_id');
                                $deptId = $get('department_id');
                                if (! $adminId || ! $deptId) return [];
                                return \App\Models\Sections::where('administrative_id', $adminId)
                                    ->where('department_id', $deptId)
                                    ->pluck('name_location', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            li
                            ->disabled(fn(callable $get) => ! $get('administrative_id') || ! $get('department_id')),

                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->native(false)
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->required(fn() => Auth::user()->isAdmin())
                            ->dehydrated(),

                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->native(false)
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->required(fn() => Auth::user()->isAdmin())
                            ->afterOrEqual('start_date')
                            ->dehydrated(),

                        Select::make('status')
                            ->label('الحالة')
                            ->options(Constans::STATUS_LABELS)
                            ->default(\App\Helpers\Constans::STATUS_NEW)
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->required(fn() => Auth::user()->isAdmin())
                            ->live()
                            ->afterStateUpdated(fn($state, $set) => $state === Constans::STATUS_CONFIRMATION ? $set('accepted_at', now()) : null),
                    ])->columns(2),

                Fieldset::make('المستندات والملاحظات')
                    ->schema([
                        FileUpload::make('application_letter')
                            ->label('صورة خطاب التدريب')
                            ->image()
                            ->disk('public')
                            ->directory('application-letters')
                            ->visibility('public')
                            ->columnSpanFull(),

                        TextInput::make('tags')
                            ->label('الوسوم')
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }
}
