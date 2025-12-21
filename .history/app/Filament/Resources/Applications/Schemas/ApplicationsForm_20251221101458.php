<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Helpers\Constans;
use App\Models\Department;
use App\Models\Institution;
use App\Models\Major;
use App\Models\College;
use App\Models\Sections;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ApplicationsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('full_name')
                    ->label('اسم المتدرب')
                    ->maxLength(255),

                TextInput::make('national_id')
                    ->label('رقم الهوية')
                    ->maxLength(20),

                TextInput::make('phone_number')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('address')
                    ->label('العنوان')
                    ->maxLength(255),

                Select::make('dob_year')
                    ->label('سنة الميلاد')
                    ->options(fn() => array_combine(
                        range(date('Y') - 60, date('Y') - 18),
                        range(date('Y') - 60, date('Y') - 18)
                    ))
                    ->searchable(),

                Select::make('dob_month')
                    ->label('شهر الميلاد')
                    ->options(array_combine(range(1, 12), ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'])),

                Select::make('dob_day')
                    ->label('يوم الميلاد')
                    ->options(fn() => array_combine(range(1, 31), range(1, 31))),

                Select::make('college_id')
                    ->label('الكلية')
                    ->options(fn() => College::all()->mapWithKeys(fn($college) => [$college->id => $college->getTranslation('name', 'ar')]))
                    ->searchable(),

                Select::make('institution_id')
                    ->label('المؤسسة')
                    ->options(fn() => Institution::all()->mapWithKeys(fn($inst) => [$inst->id => $inst->getTranslation('name', 'ar')]))
                    ->searchable(),

                Select::make('major_id')
                    ->label('التخصص')
                    ->options(fn() => Major::all()->mapWithKeys(fn($major) => [$major->id => $major->getTranslation('name', 'ar')]))
                    ->searchable(),

                Select::make('trainee_id')
                    ->label('المتدرب (إذا كان موجوداً)')
                    ->options(fn() => \App\Models\Trainees::all()->mapWithKeys(fn($trainee) => [$trainee->id => $trainee->full_name]))
                    ->searchable()
                    ->nullable(),

                Select::make('department_id')
                    ->label('الدائرة')
                    ->options(fn() => Department::all()->mapWithKeys(fn($dept) => [$dept->id => $dept->title]))
                    ->searchable()
                    ->required(),

                Select::make('section_id')
                    ->label('القسم')
                    ->options(fn() => Sections::all()->mapWithKeys(fn($section) => [$section->id => $section->name_location]))
                    ->searchable()
                    ->required(),

                Select::make('training_type')
                    ->label('نوع التدريب')
                    ->options(Constans::TRAINING_TYPE_LABELS)
                    ->required(),

                TextInput::make('duration')
                    ->label('مدة التدريب (بالأيام)')
                    ->numeric()
                    ->required(),

                DatePicker::make('start_date')
                    ->label('تاريخ البدء')
                    ->required(),

                DatePicker::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->required(),

                FileUpload::make('application_letter')
                    ->label('خطاب الطلب')
                    ->directory('applications')
                    ->nullable(),

                TextInput::make('tags')
                    ->label('الوسوم')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('street')
                    ->label('الشارع')
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }
}
