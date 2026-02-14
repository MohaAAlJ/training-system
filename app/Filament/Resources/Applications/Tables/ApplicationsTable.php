<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Tables;

use App\Filament\Exporters\ApplicationExporter;
use App\Models\Administrative;
use App\Models\Application;
use App\Models\Department;
use App\Models\Governorate;
use App\Models\Major;
use App\Models\Section;
use App\Services\ExcelImportService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ApplicationsTable
{
    public static function configure(Table $table, ?string $statusTitle = null): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->forUser(Auth::user())->with([
                'trainee',
                'section:id,name,department_id,administrative_id,active',
                'section.department:id,name',
                'section.administrative:id,name,governorate_id',
                'section.administrative.governorate:id,name',
            ]))
            ->columns(self::getTableColumns())
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير جدول الطلبات')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('secondary')
                    ->extraAttributes([
                        'class' => 'transition-all duration-300 hover:scale-105',
                        'style' => 'transition: all 0.3s ease;',
                        'onmouseover' => "this.style.backgroundColor='rgb(239 68 68)'; this.style.borderColor='rgb(239 68 68)';",
                        'onmouseout' => "this.style.backgroundColor=''; this.style.borderColor='';",
                    ])
                    ->exporter(ApplicationExporter::class)
                    ->formats(
                        Auth::user()?->isAdmin()
                            ? [ExportFormat::Xlsx, ExportFormat::Csv]
                            : [ExportFormat::Xlsx]
                    ),
            ])
            ->filters(self::getTableFilters($statusTitle))
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('الفلاتر'),
            )
            ->toggleColumnsTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('الأعمدة'),
            )
            ->actions(self::getTableActions())
            ->bulkActions(self::getBulkActions());
    }

    protected static function getTableColumns(): array
    {
        return [
            TextColumn::make('trainee.full_name')
                ->label('الاسم الكامل')
                ->searchable()
                ->sortable(),

            TextColumn::make('trainee.gender')
                ->label('الجنس')
                ->badge()
                ->sortable(),

            TextColumn::make('trainee.national_id')
                ->label('رقم الهوية')
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('section.administrative.name')
                ->label('الإدارة')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('section.name')
                ->label('القسم')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('section.department.name')
                ->label('الدائرة')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('training_type')
                ->label('نوع التدريب')
                ->badge()
                ->color(fn(int $state): string => Application::getTrainingTypeColor($state))
                ->formatStateUsing(fn(int $state): string => Application::getTrainingTypeLabel($state))
                ->searchable()
                ->sortable()
                ->toggleable()
                ->hidden(fn() => Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()),

            TextColumn::make('status')
                ->label('الحالة')
                ->badge()
                ->color(fn(int $state): string => Application::getStatusColor($state))
                ->formatStateUsing(function ($state) {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    if ($user && $user->isGeneralTrainingManager() && $state === Application::STATUS_INITIAL_APPROVE) {
                        return 'في انتظار التأكيد';
                    }
                    return Application::getStatusLabel((int) $state);
                })
                ->searchable()
                ->sortable()
                ->toggleable(),

            TextColumn::make('end_date')
                ->label('تاريخ انتهاء التدريب')
                ->date('Y-m-d')
                ->sortable()
                ->searchable()
                ->toggleable(),

            TextColumn::make('created_at')
                ->label('تاريخ الطلب')
                ->dateTime('Y-m-d')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Excel/CSV Import Action Configuration
     */
    public static function getImportExcelAction(): Action
    {
        return Action::make('import_excel')
            ->label('استيراد (Excel)')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('secondary')
            ->extraAttributes([
                'class' => 'transition-all duration-300 hover:scale-105',
                'style' => 'transition: all 0.3s ease;',
                'onmouseover' => "this.style.backgroundColor='rgb(239 68 68)'; this.style.borderColor='rgb(239 68 68)';",
                'onmouseout' => "this.style.backgroundColor=''; this.style.borderColor='';",
            ])
            ->visible(fn() => Auth::user()->isCollegeSupervisor() || Auth::user()->isAdmin())
            ->modalWidth('screen')
            ->steps([
                self::getImportUploadStep(),
                self::getImportPreviewStep(),
            ])
            ->action(function (array $data) {
                try {
                    $rows = $data['import_rows'] ?? [];
                    if (empty($rows)) {
                        Notification::make()->title('لا توجد بيانات للاستيراد')->warning()->send();
                        return;
                    }

                    $importer = new ExcelImportService();
                    $result = $importer->import($rows);

                    self::notifyImportResult($result);
                } catch (\Exception $e) {
                    Notification::make()->title('خطأ غير متوقع')->body($e->getMessage())->danger()->send();
                } finally {
                    if (!empty($data['excel_file'])) {
                        Storage::disk('local')->delete($data['excel_file']);
                    }
                }
            });
    }

    protected static function getImportUploadStep(): Step
    {
        return Step::make('upload')
            ->label('ارفع الطلبات')
            ->description('قم بتحميل القالب وتجهيز البيانات فيه ثم رفع ملف الإكسيل في المربع أدناه')
            ->schema([
                Placeholder::make('download_template_link')
                    ->label('1-تحميل القالب')
                    ->content(new HtmlString('
                        <div class="flex justify-end mb-2">
                            <a href="' . url('/excel-template') . '" target="_blank" class="text-primary-600 hover:text-primary-500 font-bold flex items-center gap-1 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                <span>تحميل القالب الفارغ</span>
                            </a>
                        </div>
                    ')),
                FileUpload::make('excel_file')
                    ->label('2-رفع ملف الإكسيل')
                    ->disk('local')
                    ->directory('imports')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if (!$state) return;

                        try {
                            $filePath = self::resolveFilePath($state);
                            $importer = new ExcelImportService();
                            $rows = $importer->getRowsForPreview($filePath);

                            // Pre-processing
                            foreach ($rows as &$row) {
                                unset($row['administrative_unit']);
                            }

                            $set('import_rows', $rows);
                        } catch (\Exception $e) {
                            Notification::make()->title('خطأ في قراءة الملك')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    protected static function getImportPreviewStep(): Step
    {
        return Step::make('preview')
            ->label('مراجعة البيانات')
            ->description('تأكد من صحة البيانات قبل الحفظ')
            ->schema([
                Repeater::make('import_rows')
                    ->label('بيانات الطلاب')
                    ->table([
                        TableColumn::make('الاسم الكامل'),
                        TableColumn::make('رقم الهوية'),
                        TableColumn::make('الجنس'),
                        TableColumn::make('الرقم الجامعي'),
                        TableColumn::make('رقم الجوال'),
                        TableColumn::make('المحافظة'),
                        TableColumn::make('التخصص'),
                        TableColumn::make('الإدارة'),
                        TableColumn::make('الدائرة'),
                        TableColumn::make('القسم'),
                        TableColumn::make('تاريخ الميلاد'),
                    ])
                    ->schema(self::getImportRepeaterSchema())
                    ->addable(false)
                    ->deletable(true)
                    ->reorderable(false)
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'max-height: 50vh; overflow-y: auto; overflow-x: hidden;'])
                    ->itemLabel(fn(array $state): ?string => $state['full_name'] ?? null),
            ]);
    }

    protected static function getImportRepeaterSchema(): array
    {
        return [
            TextInput::make('full_name')->label('الاسم الكامل')->required()->hiddenLabel()->extraInputAttributes(['tabindex' => 1]),
            TextInput::make('national_id')->label('رقم الهوية')->required()->length(9)->hiddenLabel()->extraInputAttributes(['tabindex' => 2]),
            Select::make('gender')
                ->label('الجنس')
                ->options(\App\Enums\Gender::class)
                ->required()
                ->hiddenLabel()
                ->extraInputAttributes(['tabindex' => 3]),
            TextInput::make('university_number')->label('الرقم الجامعي')->required()->integer()->minValue(1)->hiddenLabel()->extraInputAttributes(['tabindex' => 4]),
            TextInput::make('phone')->label('رقم الجوال')->required()->hiddenLabel()->extraInputAttributes(['tabindex' => 5]),
            Select::make('governorate_id')
                ->label('المحافظة')
                ->options(Governorate::pluck('name', 'id'))
                ->required()
                ->hiddenLabel()
                ->extraInputAttributes(['tabindex' => 6]),
            Select::make('major_id')
                ->label('التخصص')
                ->options(fn() => self::getMajorOptions())
                ->required()
                ->hiddenLabel()
                ->extraInputAttributes(['tabindex' => 7]),
            Select::make('administrative_id')
                ->label('الإدارة')
                ->options(Administrative::pluck('name', 'id'))
                ->required()
                ->hiddenLabel()
                ->live()
                ->afterStateUpdated(function (Set $set) {
                    $set('department_id', null);
                    $set('section_id', null);
                })
                ->extraInputAttributes(['tabindex' => 8]),
            Select::make('department_id')
                ->label('الدائرة')
                ->options(fn(Get $get) => self::getDepartmentOptions($get('administrative_id')))
                ->required()
                ->hiddenLabel()
                ->live()
                ->afterStateUpdated(fn(Set $set) => $set('section_id', null))
                ->extraInputAttributes(['tabindex' => 9]),
            Select::make('section_id')
                ->label('القسم')
                ->options(fn(Get $get) => self::getSectionOptions($get('administrative_id'), $get('department_id')))
                ->required()
                ->hiddenLabel()
                ->extraInputAttributes(['tabindex' => 10]),
            DatePicker::make('dob')
                ->label('تاريخ الميلاد')
                ->required()
                ->hiddenLabel()
                ->displayFormat('Y-m-d')
                ->native(false)
                ->closeOnDateSelection()
                ->maxDate(now()->subYears(20))
                ->minDate(now()->subYears(60))
                ->validationMessages([
                    'before_or_equal' => 'يجب أن يكون عمر المتقدم على الأقل 20 سنة.',
                    'after_or_equal' => 'يجب أن يكون عمر المتقدم 60 سنة كحد أقصى.',
                ])
                ->extraInputAttributes(['tabindex' => 11]),
        ];
    }

    protected static function getMajorOptions(): array
    {
        $user = Auth::user();
        if ($user && $user->isCollegeSupervisor() && $user->college) {
            return $user->college->majors()->pluck('name', 'majors.id')->toArray();
        }
        return Major::pluck('name', 'id')->toArray();
    }

    protected static function getDepartmentOptions(?int $adminId): array
    {
        if (!$adminId) return [];
        return Department::active()
            ->whereHas('sections', fn($q) => $q->where('administrative_id', $adminId)->active())
            ->pluck('name', 'id')
            ->toArray();
    }

    protected static function getSectionOptions(?int $adminId, ?int $deptId): array
    {
        if (!$adminId || !$deptId) return [];
        return Section::where('administrative_id', $adminId)
            ->where('department_id', $deptId)
            ->active()
            ->pluck('name', 'id')
            ->toArray();
    }

    protected static function resolveFilePath($state): string
    {
        $stateValue = is_array($state) ? reset($state) : $state;

        if ($stateValue instanceof TemporaryUploadedFile) {
            return $stateValue->getRealPath();
        }

        if (is_string($stateValue) && file_exists($stateValue) && is_readable($stateValue)) {
            return $stateValue;
        }

        return Storage::disk('local')->path((string)$stateValue);
    }

    protected static function notifyImportResult(array $result): void
    {
        if ($result['success'] > 0) {
            Notification::make()->title("تم استيراد {$result['success']} طلب بنجاح")->success()->send();
        }

        if ($result['failed'] > 0) {
            Notification::make()
                ->title("فشل استيراد {$result['failed']} طلب")
                ->body(implode("\n", array_slice($result['errors'], 0, 5)))
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected static function getTableFilters(?string $statusTitle): array
    {
        return [
            // Status Filter - Only for GTM and Admin
            SelectFilter::make('status')
                ->label('الحالة')
                ->options(Application::getStatuses())
                ->visible(fn() => $statusTitle === null && (Auth::user()->isAdmin() || Auth::user()->isGeneralTrainingManager())),

            // Hierarchical Filter
            \Filament\Tables\Filters\Filter::make('hierarchy_filter')
                ->form([
                    Select::make('administrative_id')
                        ->label('الإدارة')
                        ->options(Administrative::active()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('department_id', null);
                            $set('section_id', null);
                        })
                        ->visible(fn() => Auth::user()->isGeneralTrainingManager() || Auth::user()->isAdmin() || Auth::user()->isDepartment()),

                    Select::make('department_id')
                        ->label('الدائرة')
                        ->options(function (Get $get) {
                            $user = Auth::user();
                            if ($user->isMedicalManager()) {
                                return Department::where('is_medical', true)->pluck('name', 'id');
                            }

                            $query = Department::active();
                            if ($adminId = $get('administrative_id')) {
                                $query->whereHas('sections', fn($q) => $q->where('administrative_id', $adminId)->active());
                            }

                            return $query->pluck('name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn(Set $set) => $set('section_id', null))
                        ->visible(fn() => Auth::user()->isGeneralTrainingManager() || Auth::user()->isAdmin() || Auth::user()->isHOA() || Auth::user()->isMedicalManager()),

                    Select::make('section_id')
                        ->label('القسم')
                        ->searchable()
                        ->preload()
                        ->options(function (Get $get) {
                            $user = Auth::user();
                            $query = Section::active();

                            // Role-based restrictions
                            if ($user->isDepartment() && $user->department) {
                                $query->where('department_id', $user->department->id);
                            } elseif ($user->isHOA() && $user->administrative) {
                                $query->where('administrative_id', $user->administrative->id);
                            } elseif ($user->isMedicalManager()) {
                                $query->whereHas('department', fn($q) => $q->where('is_medical', true));
                            } elseif ($user->isSectionHead() && $user->section) {
                                $query->where('id', $user->section->id);
                            }

                            // Dependent filtering
                            if ($deptId = $get('department_id')) {
                                $query->where('department_id', $deptId);
                            } elseif ($adminId = $get('administrative_id')) {
                                $query->where('administrative_id', $adminId);
                            }

                            return $query->pluck('name', 'id');
                        })
                        ->visible(fn() => Auth::user()->isGeneralTrainingManager() || Auth::user()->isAdmin() || Auth::user()->isHOA() || Auth::user()->isDepartment() || Auth::user()->isSectionHead() || Auth::user()->isMedicalManager()),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['administrative_id'] ?? null,
                            fn(Builder $query, $adminId) => $query->whereHas('section', fn($q) => $q->where('administrative_id', $adminId))
                        )
                        ->when(
                            $data['department_id'] ?? null,
                            fn(Builder $query, $deptId) => $query->whereHas('section', fn($q) => $q->where('department_id', $deptId))
                        )
                        ->when(
                            $data['section_id'] ?? null,
                            fn(Builder $query, $sectionId) => $query->where('section_id', $sectionId)
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['administrative_id'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('الإدارة: ' . Administrative::find($data['administrative_id'])?->name)
                            ->removeField('administrative_id');
                    }
                    if ($data['department_id'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('الدائرة: ' . Department::find($data['department_id'])?->name)
                            ->removeField('department_id');
                    }
                    if ($data['section_id'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('القسم: ' . Section::find($data['section_id'])?->name)
                            ->removeField('section_id');
                    }
                    return $indicators;
                }),

            // End Date Filter - Visible to all roles
            \Filament\Tables\Filters\Filter::make('end_date')
                ->label('تاريخ الانتهاء')
                ->form([
                    DatePicker::make('end_from')
                        ->label('من تاريخ')
                        ->placeholder('اختر التاريخ')
                        ->native(false)
                        ->closeOnDateSelection(),
                    DatePicker::make('end_until')
                        ->label('إلى تاريخ')
                        ->placeholder('اختر التاريخ')
                        ->native(false)
                        ->closeOnDateSelection(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['end_from'] ?? null,
                            fn(Builder $query, $date): Builder => $query->whereDate('end_date', '>=', $date),
                        )
                        ->when(
                            $data['end_until'] ?? null,
                            fn(Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['end_from'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('الانتهاء من: ' . Carbon::parse($data['end_from'])->format('Y-m-d'))
                            ->removeField('end_from');
                    }
                    if ($data['end_until'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('الانتهاء حتى: ' . Carbon::parse($data['end_until'])->format('Y-m-d'))
                            ->removeField('end_until');
                    }
                    return $indicators;
                })
                ->visible(fn() => Auth::user()->isGeneralTrainingManager()
                    || Auth::user()->isAdmin()
                    || Auth::user()->isHOA()
                    || Auth::user()->isDepartment()
                    || Auth::user()->isSectionHead()
                    || Auth::user()->isMedicalManager()
                    || Auth::user()->isCollegeSupervisor()
                    || Auth::user()->isMinistry()),
        ];
    }

    protected static function getTableActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make()
                ->visible(
                    fn(Application $record) =>
                    ! ((Auth::user()->isMinistry() || Auth::user()->isCollegeSupervisor()) && $record->status === Application::STATUS_CONFIRMATION)
                ),

            Action::make('initial_approve')
                ->label('موافقة مبدئية')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_NEW && (Auth::user()->isGeneralTrainingManager() || Auth::user()->isMinistry()))
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_INITIAL_APPROVE, 'accepted_at' => now()]);
                    Notification::make()->title('تمت الموافقة المبدئية بنجاح')->success()->send();
                }),

            Action::make('confirm_application')
                ->label('تأكيد الطلب')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_INITIAL_APPROVE && (Auth::user()->isMinistry() || Auth::user()->isCollegeSupervisor()))
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_CONFIRMATION]);
                    Notification::make()->title('تم تأكيد الطلب بنجاح')->success()->send();
                }),

            Action::make('process_application')
                ->label('معالجة الطلب')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->visible(fn(Application $record) => in_array($record->status, [Application::STATUS_CONFIRMATION, Application::STATUS_WAITING_LIST]) && Auth::user()->isGeneralTrainingManager())
                ->form(fn(Application $record) => self::getProcessApplicationFormSchema($record))
                ->action(fn(Application $record, array $data) => self::processApplicationAction($record, $data)),

            Action::make('end_training')
                ->label('إنهاء التدريب')
                ->icon('heroicon-o-flag')
                ->color('warning')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_STARTED_TRAINING && Auth::user()->isGeneralTrainingManager())
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_ENDED_TRAINING, 'end_date' => now()]);
                    Notification::make()->title('تم إنهاء التدريب بنجاح')->success()->send();
                }),

            Action::make('reject')
                ->label('رفض الطلب')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn(Application $record) => in_array($record->status, [Application::STATUS_NEW, Application::STATUS_INITIAL_APPROVE, Application::STATUS_CONFIRMATION, Application::STATUS_WAITING_LIST]) && Auth::user()->isGeneralTrainingManager())
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_REJECTED]);
                    Notification::make()->title('تم رفض الطلب')->danger()->send();
                }),

            Action::make('restore_rejection')
                ->label('استعادة من الرفض')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_REJECTED && Auth::user()->isGeneralTrainingManager())
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_NEW]);
                    Notification::make()->title('تمت استعادة الطلب إلى جديد')->success()->send();
                }),

            Action::make('print_absorption_paper')
                ->label('طباعة ورقة الاستيعاب')
                ->icon('heroicon-o-printer')
                ->url(fn(Application $record) => route('applications.download-absorption', ['application' => $record, 'mode' => 'view']))
                ->openUrlInNewTab()
                ->visible(fn(Application $record) => Auth::user()->isMinistry() &&
                    in_array($record->status, [
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING,
                    ]) &&
                    $record->training_type === Application::PRACTICE),

            Action::make('download_absorption_paper')
                ->label('تحميل ورقة الاستيعاب')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn(Application $record) => route('applications.download-absorption', ['application' => $record, 'mode' => 'download']))
                ->openUrlInNewTab()
                ->visible(fn(Application $record) => Auth::user()->isMinistry() &&
                    in_array($record->status, [
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_STARTED_TRAINING,
                        Application::STATUS_ENDED_TRAINING,
                    ]) &&
                    $record->training_type === Application::PRACTICE),
        ];
    }

    protected static function getProcessApplicationFormSchema(Application $record): array
    {
        $schema = [];
        $sectionStats = $record->section?->getCapacityStats();
        $isSectionInactive = ! ($record->section?->active ?? false);
        $isSectionFull = $sectionStats['is_full'] ?? false;

        if ($isSectionInactive || $isSectionFull) {
            $warningTitle = $isSectionInactive ? 'تنبيه: القسم المسجل غير نشط' : 'تنبيه: القسم ممتلئ بالكامل';
            $warningMessage = $isSectionInactive
                ? 'القسم المرتبط بالطلب حالياً غير نشط.'
                : "القسم المرتبط بالطلب ممتلئ (السعة: {$sectionStats['total']}).";

            $schema[] = Placeholder::make('warning')
                ->label($warningTitle)
                ->content($warningMessage)
                ->columnSpanFull()
                ->extraAttributes(['class' => 'text-danger-600 font-bold']);

            return $schema;
        }

        $defaultStatus = match ($record->status) {
            Application::STATUS_WAITING_LIST => Application::STATUS_STARTED_TRAINING,
            Application::STATUS_CONFIRMATION => Application::STATUS_WAITING_LIST,
            default => Application::STATUS_STARTED_TRAINING,
        };

        $schema[] = Select::make('new_status')
            ->label('الحالة الجديدة')
            ->options([
                Application::STATUS_STARTED_TRAINING => Application::getStatusLabel(Application::STATUS_STARTED_TRAINING),
                Application::STATUS_WAITING_LIST => Application::getStatusLabel(Application::STATUS_WAITING_LIST),
            ])
            ->required()
            ->reactive();
        $schema[] = DatePicker::make('start_date')
            ->label('تاريخ بدء التدريب')
            ->required()
            ->visible(fn(Get $get) => (int)$get('new_status') == Application::STATUS_STARTED_TRAINING)
            ->default(now()->toDateString())
            ->displayFormat('Y/m/d')
            ->native(false)
            ->closeOnDateSelection()
            ->reactive();

        $schema[] = TextInput::make('training_duration')
            ->label('مدة التدريب (بالأيام)')
            ->numeric()
            ->required()
            ->visible(fn(Get $get) => (int)$get('new_status') == Application::STATUS_STARTED_TRAINING)
            ->default(30)
            ->live();

        $schema[] = Placeholder::make('expected_finish_date_placeholder')
            ->label('تاريخ الانتهاء المتوقع')
            ->content(function (Get $get) {
                $start = $get('start_date');
                $duration = $get('training_duration');
                if ($start && $duration) {
                    return Carbon::parse($start)->addDays((int)$duration)->toDateString();
                }
                return 'يرجى تحديد تاريخ البدء ومدة التدريب';
            })
            ->visible(fn(Get $get) => (int)$get('new_status') == Application::STATUS_STARTED_TRAINING);

        return $schema;
    }

    protected static function processApplicationAction(Application $record, array $data): void
    {
        if (! isset($data['new_status'])) {
            return;
        }

        $updateData = ['status' => $data['new_status']];

        if ($data['new_status'] == Application::STATUS_STARTED_TRAINING) {
            $updateData['start_date'] = $data['start_date'];
            $updateData['end_date'] = Carbon::parse($data['start_date'])->addDays((int)$data['training_duration']);
        }

        $record->update($updateData);
        Notification::make()->title('تمت معالجة الطلب بنجاح')->success()->send();
    }

    protected static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                \Filament\Actions\BulkAction::make('change_status')
                    ->label('تغيير الحالة')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(function ($livewire) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        $activeTab = $livewire->activeTab ?? 'all';

                        // 1. Never show on "All" tab or if user is unknown
                        if ($activeTab === 'all' || !$user) {
                            return false;
                        }

                        // 2. Admins and GTM see it on any status-specific tab (except terminal ones)
                        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
                            $excluded = ['university_training', 'practice_training', 'rejected', 'finished'];

                            if ($user->isGeneralTrainingManager()) {
                                $excluded[] = 'initial_approve';
                            }

                            return !in_array($activeTab, $excluded);
                        }

                        // 3. College Supervisors and Ministry only see it in "Initial Approval" context
                        if (($user->isCollegeSupervisor() || $user->isMinistry()) && $activeTab === 'initial_approve') {
                            return true;
                        }

                        // 4. HOD and Section Heads only see it in "Waiting List" context
                        if (($user->isDepartment() || $user->isSectionHead()) && $activeTab === 'waiting_list') {
                            return true;
                        }

                        return false;
                    })
                    ->form(function ($livewire) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        $activeTab = $livewire->activeTab ?? 'all';
                        $options = Application::getStatuses();

                        switch ($activeTab) {
                            case 'new':
                                $options = [Application::STATUS_INITIAL_APPROVE => Application::getStatusLabel(Application::STATUS_INITIAL_APPROVE)];
                                break;
                            case 'initial_approve':
                                $options = [Application::STATUS_CONFIRMATION => Application::getStatusLabel(Application::STATUS_CONFIRMATION)];
                                break;
                            case 'confirmed':
                                $options = [
                                    Application::STATUS_WAITING_LIST => Application::getStatusLabel(Application::STATUS_WAITING_LIST),
                                    Application::STATUS_STARTED_TRAINING => Application::getStatusLabel(Application::STATUS_STARTED_TRAINING),
                                ];
                                break;
                            case 'waiting_list':
                                $options = [Application::STATUS_STARTED_TRAINING => Application::getStatusLabel(Application::STATUS_STARTED_TRAINING)];
                                break;
                            case 'training':
                                $options = [Application::STATUS_ENDED_TRAINING => Application::getStatusLabel(Application::STATUS_ENDED_TRAINING)];
                                break;
                        }

                        if ($user && ($user->isCollegeSupervisor() || $user->isMinistry())) {
                            if ($activeTab === 'initial_approve' || $activeTab === 'all') {
                                $options = [Application::STATUS_CONFIRMATION => Application::getStatusLabel(Application::STATUS_CONFIRMATION)];
                            }
                        }

                        return [
                            Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options($options)
                                ->default(fn() => (is_array($options) && count($options) === 1) ? array_key_first($options) : null)
                                ->required()
                                ->reactive(),
                            DatePicker::make('start_date')
                                ->label('تاريخ بدء التدريب')
                                ->visible(fn(Get $get) => in_array((int)$get('status'), [Application::STATUS_STARTED_TRAINING]))
                                ->required()
                                ->default(now()->toDateString())
                                ->live(),
                            TextInput::make('duration')
                                ->label('مدة التدريب (بالأيام)')
                                ->numeric()
                                ->visible(fn(Get $get) => in_array((int)$get('status'), [Application::STATUS_STARTED_TRAINING]))
                                ->required()
                                ->default(30)
                                ->live(),
                            Placeholder::make('expected_finish_date')
                                ->label('تاريخ الانتهاء المتوقع')
                                ->content(function (Get $get) {
                                    $start = $get('start_date');
                                    $duration = $get('duration');
                                    if ($start && $duration) {
                                        return Carbon::parse($start)->addDays((int)$duration)->toDateString();
                                    }
                                    return 'يرجى تحديد تاريخ البدء ومدة التدريب';
                                })
                                ->visible(fn(Get $get) => in_array((int)$get('status'), [Application::STATUS_STARTED_TRAINING])),
                        ];
                    })
                    ->action(function (Collection $records, array $data) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        $status = (int)$data['status'];

                        $count = 0;
                        $records->each(function (Application $record) use ($user, $status, $data, &$count) {
                            // STRICT PERMISSION & FLOW VALIDATION
                            $allowed = false;

                            if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
                                $allowed = true; // Admins/GTM can do anything
                            } elseif (($user->isCollegeSupervisor() || $user->isMinistry()) && $record->status === Application::STATUS_INITIAL_APPROVE) {
                                $allowed = true; // Supervisors move Initial -> Confirmation
                            } elseif (($user->isDepartment() || $user->isSectionHead()) && $record->status === Application::STATUS_WAITING_LIST) {
                                $allowed = true; // HOD/Section move Waiting -> Training
                            }

                            if (!$allowed) {
                                return;
                            }

                            $updateData = ['status' => $status];

                            if ($status === Application::STATUS_STARTED_TRAINING) {
                                $updateData['start_date'] = $data['start_date'];
                                $updateData['end_date'] = Carbon::parse($data['start_date'])->addDays((int)$data['duration']);
                            }

                            $record->update($updateData);
                            $count++;
                        });

                        if ($count > 0) {
                            Notification::make()->title("تم تحديث {$count} طلب بنجاح")->success()->send();
                        } else {
                            Notification::make()->title('لم يتم تحديث أي طلب (قد تكون الطلبات المختارة غير مؤهلة لرتبتك)')
                                ->warning()
                                ->send();
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ]),
        ];
    }
}
