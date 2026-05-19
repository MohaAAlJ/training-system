<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Tables;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Exporters\ApplicationExporter;
use App\Models\Administrative;
use App\Models\Application;
use App\Models\Department;
use App\Models\Governorate;
use App\Models\Institution;
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
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Rules\ValidMultipleApplications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Notifications\ApplicationFilesUploadedNotification;
use App\Rules\PalestinianId;
use Illuminate\Support\Facades\Log;

class ApplicationsTable
{
    public static function configure(Table $table, ?string $statusTitle = null): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->select('applications.*')->latest()->with([
                'trainee',
                'section:id,name,administrative_id,active',
                'section.departments' => fn($q) => $q->visible()->select('departments.id', 'departments.name', 'departments.visible', 'departments.moh_dept_user_id'),
                'section.administrative:id,name,governorate_id',
                'section.administrative.governorate:id,name',
                'college:id,name,institution_id,user_id',
                'college.user:id,name,role',
                'institution:id,name',
                'major:id,name',
                'media',
            ]))
            ->columns(self::getTableColumns())
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn(Application $record): string => ApplicationResource::getUrl('view', ['record' => $record->id]))
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

            TextColumn::make('trainee.national_id')
                ->label('رقم الهوية')
                ->searchable()
                ->sortable(),

            TextColumn::make('trainee.gender')
                ->label('الجنس')
                ->badge()
                ->sortable(),

            TextColumn::make('section.administrative.name')
                ->label('الإدارة')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('section.name')
                ->label('القسم')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: fn() => ! (Auth::check() && Auth::user()->isHOA())),

            TextColumn::make('section.departments.name')
                ->label('الدوائر')
                ->state(fn(Application $record): array => $record->section?->departments
                    ?->filter(fn(Department $department): bool => $department->visible
                        && ! ($department->moh_dept_user_id !== null && str_contains($department->name, ' - ')))
                    ->pluck('name')
                    ->all() ?? [])
                ->badge()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('college.name')
                ->label('الكلية')
                ->description(fn(Application $record) => $record->institution?->name)
                ->searchable()
                ->sortable()
                ->visible(fn() => Auth::check() && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('major.name')
                ->label('التخصص')
                ->searchable()
                ->sortable()
                ->visible(fn() => Auth::check() && (Auth::user()->isCollegeSupervisor() || Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->toggleable(isToggledHiddenByDefault: false),

            TextColumn::make('training_hours')
                ->label('ساعات التدريب')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('training_days_count')
                ->label('عدد أيام التدريب في الأسبوع')
                ->state(function (Application $record) {
                    $days = $record->days_note['training_days'] ?? [];
                    return count((array)$days);
                })
                ->badge()
                ->color('info')
                ->toggleable(isToggledHiddenByDefault: false),

            TextColumn::make('training_type')
                ->label('نوع التدريب')
                ->badge()
                ->color(fn(int $state): string => Application::getTrainingTypeColor($state))
                ->formatStateUsing(fn(int $state): string => Application::getTrainingTypeLabel($state))
                ->searchable()
                ->sortable()
                ->toggleable()
                ->hidden(fn() => Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()),

            TextColumn::make('days_note.training_days')
                ->label('أيام التدريب')
                ->badge()
                ->state(function (Application $record) {
                    $state = $record->days_note['training_days'] ?? [];
                    if (empty($state)) {
                        return null;
                    }
                    $days = (array) $state;
                    $validDays = array_filter($days, fn($day) => isset(Application::ALL_DAYS[(int)$day]));
                    if (empty($validDays)) {
                        return null;
                    }
                    $validDays = array_map('intval', $validDays);
                    sort($validDays);

                    $hasFriday = in_array(Application::DAY_FRIDAY, $validDays, true);
                    $hasSaturday = in_array(Application::DAY_SATURDAY, $validDays, true);

                    if (count($validDays) === 7) {
                        return ['كل أيام الأسبوع'];
                    }

                    if (count($validDays) === 5 && !$hasFriday && !$hasSaturday) {
                        return ['أيام التدريب'];
                    }

                    return array_map(fn($day) => Application::ALL_DAYS[$day], $validDays);
                })
                ->color(function (string $state): string {
                    return match ($state) {
                        'أيام التدريب' => 'success',
                        'الأحد', 'الثلاثاء', 'الخميس' => 'info',
                        'غير محدد' => 'gray',
                        default => 'danger',
                    };
                })
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('status')
                ->label('الحالة')
                ->badge()
                ->color(fn(int $state): string => Application::getStatusColor($state))
                ->formatStateUsing(function ($state) {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    if ($user && $user->isTrainingManagerLike() && $state === Application::STATUS_INITIAL_APPROVE) {
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
            ->visible(fn() => Auth::user()->isCollegeSupervisor() || Auth::user()->isAdmin() || Auth::user()->isMinistry())
            ->modalWidth('screen')
            ->steps([
                self::getImportUploadStep(),
                self::getImportPreviewStep(),
            ])
            ->action(function (array $data) {
                ini_set('memory_limit', '512M');

                // Strip ghost rows left behind by the Repeater when the user deletes a row.
                // Deleted rows remain in the array as empty stubs with no national_id/full_name.
                $rows = array_values(array_filter(
                    $data['import_rows'] ?? [],
                    fn($row) => !empty($row['national_id']) || !empty($row['full_name'])
                ));

                if (empty($rows)) {
                    Notification::make()->title('لا توجد بيانات للاستيراد')->warning()->send();
                    return;
                }

                // Field-level validation (national_id rules + section_id required) already
                // blocked any invalid rows before action() was reached, so we can import directly.
                try {
                    $importer = new ExcelImportService();
                    $result   = $importer->import($rows);

                    if ($result['success'] > 0) {
                        Notification::make()
                            ->title("تم استيراد {$result['success']} طلب بنجاح")
                            ->success()
                            ->send();
                    }

                    if ($result['failed'] > 0) {
                        Notification::make()
                            ->title("فشل استيراد {$result['failed']} طلب")
                            ->body(implode("\n", array_slice($result['errors'], 0, 10)))
                            ->danger()
                            ->persistent()
                            ->send();
                    }
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
                        if (!$state) {
                            $set('import_rows', []);
                            return;
                        }

                        try {
                            $filePath = self::resolveFilePath($state);
                            $importer = new ExcelImportService();
                            $rows = $importer->getRowsForPreview($filePath);

                            // Pre-processing: remove internal keys not needed by the repeater
                            $retrievedNames = [];
                            foreach ($rows as &$row) {
                                if (! empty($row['is_existing_trainee'])) {
                                    $retrievedNames[] = $row['full_name'];
                                }
                                unset($row['administrative_unit']); // keep is_existing_trainee for schema
                            }
                            unset($row);

                            $set('import_rows', $rows);

                            // Notify about auto-retrieved trainee data
                            if (! empty($retrievedNames)) {
                                $count = count($retrievedNames);
                                $nameList = implode('، ', array_slice($retrievedNames, 0, 10));
                                $extra = $count > 10 ? " وآخرون..." : '';

                                Notification::make()
                                    ->title("تم استرجاع بيانات {$count} متدرب من السجلات السابقة")
                                    ->body("تم تعبئة البيانات الشخصية تلقائياً لـ: {$nameList}{$extra}")
                                    ->success()
                                    ->persistent()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()->title('خطأ في قراءة الملك')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    protected static function getImportPreviewStep(): Step
    {
        $isMoh = Auth::user()->isMinistry();

        $schema = $isMoh
            ? self::getMohImportRepeaterSchema()
            : self::getCollegeImportRepeaterSchema();

        return Step::make('preview')
            ->label('مراجعة البيانات')
            ->description(fn(Get $get) => ($count = count($get('import_rows') ?? [])) > 0
                ? "تم استخراج {$count} طلبات. تأكد من صحة البيانات قبل الحفظ."
                : 'يرجى رفع ملف الإكسيل ومراجعته هنا.')
            ->schema([
                // ── Inject table-row CSS for the repeater ──────────────────────
                Placeholder::make('_repeater_styles')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->content(new HtmlString('<style>
                        /* The whole repeater wrapper scrolls both directions */
                        #import-repeater-wrapper {
                            overflow-x: auto !important;
                            overflow-y: auto !important;
                        }
                        /* Items must NOT clip — they stay full-width */
                        #import-repeater-wrapper .fi-fo-repeater-item {
                            overflow: visible !important;
                            min-width: max-content;
                        }
                        /* Make each item\'s field container a single non-wrapping row */
                        #import-repeater-wrapper .fi-fo-repeater-item .fi-fo-component-ctn,
                        #import-repeater-wrapper .fi-fo-repeater-item [class*="grid"] {
                            display: flex !important;
                            flex-wrap: nowrap !important;
                            gap: 0.75rem;
                            align-items: flex-start;
                            overflow: visible !important;
                        }
                        /* Each field cell keeps its min-width and never shrinks */
                        #import-repeater-wrapper .fi-fo-repeater-item .fi-fo-field-wrp {
                            flex: 0 0 auto;
                        }
                        /* Scrollbar styling */
                        #import-repeater-wrapper {
                            /* Firefox support */
                            scrollbar-width: thin;
                            scrollbar-color: rgb(var(--primary-500)) rgba(156, 163, 175, 0.2);
                        }
                        #import-repeater-wrapper::-webkit-scrollbar {
                            height: 12px;
                            width: 12px;
                        }
                        #import-repeater-wrapper::-webkit-scrollbar-track {
                            background: rgba(156, 163, 175, 0.2); /* Gray track */
                            border-radius: 8px;
                        }
                        #import-repeater-wrapper::-webkit-scrollbar-thumb {
                            background-color: var(--primary-500, rgb(59, 130, 246)); /* Fallback to blue if var undefined */
                            border-radius: 8px;
                            border: 3px solid transparent;
                            background-clip: padding-box;
                        }
                        /* Support for rgb split vars like rgba(var(--primary-500), 1) used in newer tailwind */
                        @supports (background-color: rgb(var(--primary-500))) {
                            #import-repeater-wrapper::-webkit-scrollbar-thumb {
                                background-color: rgb(var(--primary-500));
                            }
                        }
                        #import-repeater-wrapper::-webkit-scrollbar-thumb:hover {
                            background-color: var(--primary-600, rgb(37, 99, 235));
                        }
                        @supports (background-color: rgb(var(--primary-600))) {
                            #import-repeater-wrapper::-webkit-scrollbar-thumb:hover {
                                background-color: rgb(var(--primary-600));
                            }
                        }
                    </style>')),

                TextInput::make('_import_search')
                    ->hiddenLabel()
                    ->placeholder('🔍  بحث بالاسم أو رقم الهوية...')
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->extraInputAttributes([
                        'id'      => 'import-search-input',
                        'oninput' => "
                            const q = this.value.toLowerCase().trim();
                            document.querySelectorAll('#import-repeater-wrapper .fi-fo-repeater-item')
                                .forEach(function(item) {
                                    item.style.display =
                                        (!q || item.innerText.toLowerCase().includes(q))
                                            ? ''
                                            : 'none';
                                });
                        ",
                    ]),

                Repeater::make('import_rows')
                    ->default([])
                    ->label('بيانات الطلاب')
                    ->schema($schema)
                    ->addable(false)
                    ->deletable(true)
                    ->reorderable(false)
                    ->columnSpanFull()
                    ->itemLabel(
                        fn(array $state): string =>
                        trim(($state['full_name'] ?? '') . ' — ' . ($state['national_id'] ?? ''))
                            ?: 'متدرب جديد'
                    )
                    ->extraAttributes([
                        'id'    => 'import-repeater-wrapper',
                        'style' => 'max-height: 42vh; overflow-x: auto; overflow-y: auto;',
                    ]),
            ]);
    }

    /**
     * Schema for MOH users — no university_number or major_id columns.
     * Field ORDER must match getImportPreviewStep() MOH table headers exactly.
     */
    protected static function getMohImportRepeaterSchema(): array
    {

        $user           = Auth::user();
        $linkedDept     = $user->mohDepartment;
        $linkedAdminId  = null;

        if ($linkedDept) {
            // Resolve the administrative from the department's first section
            $section       = Section::whereHas('departments', fn($q) => $q->where('departments.id', $linkedDept->id)->visible())->first();
            $linkedAdminId = $section?->administrative_id;
        }

        return [
            // Hidden carrier — set by getRowsForPreview for existing trainees
            Hidden::make('is_existing_trainee')->default(false),

            // 1 — الاسم الكامل
            TextInput::make('full_name')
                ->label('الاسم الكامل')
                ->required()
                ->regex('/^[A-Za-z\p{Arabic}\s]+$/u')
                ->maxLength(255)
                ->validationMessages([
                    'required' => trans('validation.custom.full_name.required', [], 'ar'),
                    'regex'    => trans('validation.custom.full_name.regex', [], 'ar'),
                    'max'      => trans('validation.custom.full_name.max', [], 'ar'),
                ])
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 200px'])
                ->extraInputAttributes(['tabindex' => 1]),

            // 2 — رقم الهوية
            TextInput::make('national_id')
                ->label('رقم الهوية')
                ->required()
                ->numeric()
                ->minLength(9)
                ->maxLength(9)
                ->regex('/^\d{9}$/')
                ->rules([
                    new PalestinianId(),
                    new ValidMultipleApplications(),
                ])
                ->validationMessages([
                    'regex'      => trans('validation.custom.national_id.regex', [], 'ar'),
                    'max' => trans('validation.custom.national_id.max', [], 'ar'),
                    'min' => trans('validation.custom.national_id.min', [], 'ar'),
                ])
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 160px'])
                ->extraInputAttributes(['tabindex' => 2]),

            // 3 — الجنس
            Select::make('gender')
                ->label('الجنس')
                ->options(\App\Enums\Gender::class)
                ->required()
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 140px'])
                ->extraInputAttributes(['tabindex' => 3]),

            // 4 — ساعات التدريب
            TextInput::make('training_hours')
                ->label('ساعات التدريب')
                ->extraInputAttributes([
                    'inputmode' => 'numeric',
                    'pattern'   => '[0-9]*',
                    'oninput'   => 'this.value = this.value.replace(/[^0-9]/g, "")',
                    'maxlength' => '3',
                    'tabindex'  => 4
                ])
                ->suffix('ساعة')
                ->helperText('الساعات الأكاديمية المطلوبة (أقل من 1000)')
                ->required()
                ->integer()
                ->minValue(1)
                ->maxValue(1000)
                ->extraAttributes(['style' => 'min-width: 150px']),

            // 5 — رقم الجوال
            TextInput::make('phone')
                ->label('رقم الجوال')
                ->tel()
                ->regex('/^97(0|2)5\d{8}$/')
                ->minLength(12)
                ->maxLength(12)
                ->validationMessages([
                    'regex' => trans('validation.custom.phone_number.regex', [], 'ar'),
                ])
                ->required()
                ->extraAttributes(['style' => 'min-width: 170px'])
                ->extraInputAttributes(['tabindex' => 5]),

            // 6 — المحافظة
            Select::make('governorate_id')
                ->label('المحافظة')
                ->options(fn() => self::getGovernorateOptions())
                ->required()
                ->extraAttributes(['style' => 'min-width: 160px'])
                ->extraInputAttributes(['tabindex' => 6]),

            TextInput::make('street')
                ->label('الشارع')
                ->maxLength(255)
                ->rules(['nullable', 'regex:/^[A-Za-z\p{Arabic}0-9\s\-\.,#\/_]+$/u'])
                ->validationMessages([
                    'regex' => 'صيغة الشارع غير صحيحة.',
                    'max' => 'طول الشارع يجب ألا يتجاوز :max حرفًا.',
                ])
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 180px'])
                ->extraInputAttributes(['tabindex' => 6]),

            // 7 — الإدارة (auto-selected + locked for linked MOH)
            Select::make('administrative_id')
                ->label('الإدارة')
                ->options(fn() => self::getAdministrativeOptions())
                ->default($linkedAdminId)
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set) {
                    $set('department_id', null);
                    $set('section_id', null);
                })
                ->extraAttributes(['style' => 'min-width: 180px'])
                ->extraInputAttributes(['tabindex' => 7]),

            // 8 — الدائرة (medical only; hidden + pre-filled for linked MOH)
            Select::make('department_id')
                ->label('الدائرة')
                ->options(fn(Get $get) => self::getDepartmentOptions($get('administrative_id') ?? $linkedAdminId))
                ->default($linkedDept?->id)
                ->disabled((bool) $linkedDept)
                ->hidden((bool) $linkedDept)
                ->dehydrated()
                ->required(! (bool) $linkedDept)
                ->live()
                ->afterStateUpdated(fn(Set $set) => $set('section_id', null))
                ->extraAttributes(['style' => 'min-width: 200px'])
                ->extraInputAttributes(['tabindex' => 8]),

            // 9 — القسم
            Select::make('section_id')
                ->label('القسم')
                ->options(function (Get $get) use ($linkedAdminId, $linkedDept) {
                    $adminId = $get('administrative_id') ?? $linkedAdminId;
                    if (! $adminId) {
                        return [];
                    }
                    $deptId = $linkedDept ? $linkedDept->id : ($get('department_id') ?: null);
                    return self::getSectionOptions($adminId, $deptId);
                })
                ->disabled(function (Get $get) use ($linkedAdminId, $linkedDept) {
                    $adminId = $get('administrative_id') ?? $linkedAdminId;
                    if (! $adminId) return true;
                    // For unlinked MOH, also require a department to be selected
                    if (! $linkedDept && ! $get('department_id')) return true;
                    return false;
                })
                ->disableOptionWhen(function ($value) {
                    $cached = self::$sectionStatusCache[$value] ?? null;
                    if (!$cached) return false;
                    return !$cached['active'] || $cached['is_full'];
                })
                ->required()
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 180px'])
                ->extraInputAttributes(['tabindex' => 9]),

            // 10 — تاريخ الميلاد
            DatePicker::make('dob')
                ->label('تاريخ الميلاد')
                ->required()
                ->displayFormat('Y-m-d')
                ->native(false)
                ->closeOnDateSelection()
                ->maxDate(now()->subYears(18))
                ->minDate(now()->subYears(60))
                ->validationMessages([
                    'before_or_equal' => trans('validation.custom.dob.before_or_equal', [], 'ar'),
                    'after_or_equal'  => trans('validation.custom.dob.after_or_equal', [], 'ar'),
                ])
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 170px'])
                ->extraInputAttributes(['tabindex' => 10]),

            // 11 — أيام التدريب
            Select::make('days_note.training_days')
                ->label('أيام التدريب')
                ->options(Application::ALL_DAYS)
                ->multiple()
                ->searchable()
                ->preload()
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 600px']),

            // 12 — ملاحظات التدريب
            Textarea::make('days_note.note')
                ->label('ملاحظات التدريب')
                ->rows(2)
                ->extraAttributes(['style' => 'min-width: 300px']),
        ];
    }

    /**
     * Schema for College Supervisors / Admins — full 11-column layout.
     * Field ORDER must match getImportPreviewStep() non-MOH table headers exactly.
     */
    protected static function getCollegeImportRepeaterSchema(): array
    {
        ini_set('memory_limit', '512M'); // Temporarily increase memory limit for heavy repeater rendering

        return [
            // Hidden carrier — set by getRowsForPreview for existing trainees
            Hidden::make('is_existing_trainee')->default(false),

            // 1 — الاسم الكامل
            TextInput::make('full_name')
                ->label('الاسم الكامل')
                ->required()
                ->regex('/^[A-Za-z\p{Arabic}\s]+$/u')
                ->maxLength(255)
                ->validationMessages([
                    'required' => 'الاسم الكامل مطلوب.',
                    'regex'    => 'الاسم يجب أن يحتوي على أحرف ومسافات فقط.',
                    'max'      => 'طول الاسم يجب ألا يتجاوز :max حرفًا.',
                ])
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 200px'])
                ->extraInputAttributes(['tabindex' => 1]),

            // 2 — رقم الهوية
            TextInput::make('national_id')
                ->label('رقم الهوية')
                ->required()
                ->numeric()
                ->minLength(9)
                ->maxLength(9)
                ->regex('/^\d{9}$/')
                ->rules([
                    new PalestinianId(),
                    new ValidMultipleApplications(),
                ])
                ->validationMessages([
                    'regex'      => trans('validation.custom.national_id.regex', [], 'ar'),
                    'max' => trans('validation.custom.national_id.max', [], 'ar'),
                    'min' => trans('validation.custom.national_id.min', [], 'ar'),
                ])
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 160px'])
                ->extraInputAttributes(['tabindex' => 2]),

            // 3 — الجنس
            Select::make('gender')
                ->label('الجنس')
                ->options(\App\Enums\Gender::class)
                ->required()
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 140px'])
                ->extraInputAttributes(['tabindex' => 3]),

            // 4 — الرقم الجامعي
            TextInput::make('university_number')
                ->label('الرقم الجامعي')
                ->maxLength(255)
                ->required()
                ->extraAttributes(['style' => 'min-width: 160px'])
                ->extraInputAttributes(['tabindex' => 4]),

            // 5 — ساعات التدريب
            TextInput::make('training_hours')
                ->label('ساعات التدريب')
                ->extraInputAttributes([
                    'inputmode' => 'numeric',
                    'pattern'   => '[0-9]*',
                    'oninput'   => 'this.value = this.value.replace(/[^0-9]/g, "")',
                    'maxlength' => '3',
                    'tabindex'  => 5
                ])
                ->suffix('ساعة')
                ->helperText('الساعات الأكاديمية المطلوبة (أقل من 1000)')
                ->required()
                ->integer()
                ->minValue(1)
                ->maxValue(1000)
                ->extraAttributes(['style' => 'min-width: 150px']),

            // 6 — رقم الجوال
            TextInput::make('phone')
                ->label('رقم الجوال')
                ->tel()
                ->regex('/^97(0|2)5\d{8}$/')
                ->minLength(12)
                ->maxLength(12)
                ->validationMessages([
                    'regex' => trans('validation.custom.phone_number.regex', [], 'ar'),
                ])
                ->required()
                ->extraAttributes(['style' => 'min-width: 170px'])
                ->extraInputAttributes(['tabindex' => 6]),

            // 7 — المحافظة
            Select::make('governorate_id')
                ->label('المحافظة')
                ->options(fn() => self::getGovernorateOptions())
                ->required()
                ->extraAttributes(['style' => 'min-width: 160px'])
                ->extraInputAttributes(['tabindex' => 7]),

            TextInput::make('street')
                ->label('الشارع')
                ->maxLength(255)
                ->rules(['nullable', 'regex:/^[A-Za-z\p{Arabic}0-9\s\-\.,#\/_]+$/u'])
                ->validationMessages([
                    'regex' => 'صيغة الشارع غير صحيحة.',
                    'max' => 'طول الشارع يجب ألا يتجاوز :max حرفًا.',
                ])
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 180px'])
                ->extraInputAttributes(['tabindex' => 7]),

            // 8 — التخصص
            Select::make('major_id')
                ->label('التخصص')
                ->options(fn() => self::getMajorOptions())
                ->validationMessages([
                    'required' => trans('validation.custom.major_id.required', [], 'ar'),
                ])
                ->required()
                ->extraAttributes(['style' => 'min-width: 160px'])
                ->extraInputAttributes(['tabindex' => 8]),

            // 9 — الإدارة
            Select::make('administrative_id')
                ->label('الإدارة')
                ->options(fn() => self::getAdministrativeOptions())
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set) {
                    $set('section_id', null);
                })
                ->extraAttributes(['style' => 'min-width: 180px'])
                ->extraInputAttributes(['tabindex' => 9]),

            // 11 — القسم
            Select::make('section_id')
                ->label('القسم')
                ->options(fn(Get $get) => self::getSectionOptions($get('administrative_id'), null))
                ->disableOptionWhen(function ($value) {
                    $cached = self::$sectionStatusCache[$value] ?? null;
                    if (!$cached) return false;
                    return !$cached['active'] || $cached['is_full'];
                })
                ->disabled(fn(Get $get) => empty($get('administrative_id')))
                ->required()
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 180px'])
                ->extraInputAttributes(['tabindex' => 11]),

            // 12 — تاريخ الميلاد
            DatePicker::make('dob')
                ->label('تاريخ الميلاد')
                ->required()
                ->displayFormat('Y-m-d')
                ->native(false)
                ->closeOnDateSelection()
                ->maxDate(now()->subYears(18))
                ->minDate(now()->subYears(60))
                ->validationMessages([
                    'before_or_equal' => trans('validation.custom.dob.before_or_equal', [], 'ar'),
                    'after_or_equal'  => trans('validation.custom.dob.after_or_equal', [], 'ar'),
                ])
                ->disabled(fn(Get $get) => (bool) $get('is_existing_trainee'))
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 170px'])
                ->extraInputAttributes(['tabindex' => 12]),

            // 13 — أيام التدريب
            Select::make('days_note.training_days')
                ->label('أيام التدريب')
                ->options(Application::ALL_DAYS)
                ->multiple()
                ->searchable()
                ->preload()
                ->dehydrated()
                ->extraAttributes(['style' => 'min-width: 600px', 'tabindex' => 13]),

            // 14 — ملاحظات التدريب
            Textarea::make('days_note.note')
                ->label('ملاحظات التدريب')
                ->rows(2)
                ->extraAttributes(['style' => 'min-width: 300px']),
        ];
    }

    protected static array $majorOptionsCache = [];
    protected static array $governorateOptionsCache = [];
    protected static array $administrativeOptionsCache = [];
    protected static array $departmentOptionsCache = [];
    protected static array $sectionOptionsCache = [];
    protected static array $sectionStatusCache = [];

    protected static function getMajorOptions(): array
    {
        if (isset(self::$majorOptionsCache['options'])) {
            return self::$majorOptionsCache['options'];
        }

        $user = Auth::user();
        if ($user && $user->isCollegeSupervisor() && $user->college) {
            $options = $user->college->majors()
                ->wherePivot('active', true)
                ->pluck('name', 'majors.id')
                ->toArray();
        } else {
            $options = Major::pluck('name', 'id')->toArray();
        }

        self::$majorOptionsCache['options'] = $options;
        return $options;
    }

    protected static function getGovernorateOptions(): array
    {
        if (isset(self::$governorateOptionsCache['options'])) {
            return self::$governorateOptionsCache['options'];
        }
        $options = Governorate::pluck('name', 'id')->toArray();
        self::$governorateOptionsCache['options'] = $options;
        return $options;
    }

    /**
     * Administrative options for the import wizard.
     * MOH users: only admins that have sections in medical departments.
     * All others: all active admins.
     */
    protected static function getAdministrativeOptions(): array
    {
        if (isset(self::$administrativeOptionsCache['admin'])) {
            return self::$administrativeOptionsCache['admin'];
        }

        $query = Administrative::active();

        if (Auth::user()->isMinistry()) {
            $query->whereHas('sections', fn($q) => $q->whereHas(
                'departments',
                fn($d) => $d->where('is_medical', true)
            ));
        }

        $options = $query->pluck('name', 'id')->toArray();
        self::$administrativeOptionsCache['admin'] = $options;
        return $options;
    }

    /**
     * Department options for the import wizard.
     * MOH users: only is_medical departments under the given admin.
     * All others: all active departments under the given admin.
     */
    protected static function getDepartmentOptions(?int $adminId): array
    {
        if (! $adminId) {
            return [];
        }

        $cacheKey = "dept_{$adminId}";
        if (isset(self::$departmentOptionsCache[$cacheKey])) {
            return self::$departmentOptionsCache[$cacheKey];
        }

        $query = Department::active()
            ->whereHas('sections', fn($q) => $q->where('administrative_id', $adminId)->active());

        if (Auth::user()->isMinistry()) {
            $query->where('is_medical', true)
                ->whereNotNull('moh_dept_user_id');
        }

        $options = $query->pluck('name', 'id')->toArray();
        self::$departmentOptionsCache[$cacheKey] = $options;
        return $options;
    }

    protected static function getSectionOptions(mixed $adminId, mixed $deptId): array
    {
        if (!$adminId) return [];

        $cacheKey = "sec_{$adminId}_{$deptId}";
        if (isset(self::$sectionOptionsCache[$cacheKey])) {
            return self::$sectionOptionsCache[$cacheKey];
        }

        $query = Section::where('administrative_id', (int)$adminId)
            ->active()
            ->withCount(['applications' => fn($q) => $q->where('status', Application::STATUS_STARTED_TRAINING)]);

        if ($deptId) {
            $query->whereHas('departments', fn($q) => $q->where('departments.id', $deptId)->visible());
        }

        $options = $query->get()->mapWithKeys(function ($section) {
            $label = $section->name;
            $activeApplications = $section->applications_count;
            $isFull = $section->capacity <= $activeApplications;

            self::$sectionStatusCache[$section->id] = [
                'active' => $section->active,
                'is_full' => $isFull,
            ];

            if ($isFull) {
                $label .= ' (ممتلئ)';
            }

            return [$section->id => $label];
        })->toArray();

        self::$sectionOptionsCache[$cacheKey] = $options;
        return $options;
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

    // notifyImportResult removed — errors are now surfaced inline in Step 2 via the import_errors Placeholder.

    protected static function getTableFilters(?string $statusTitle): array
    {
        return [
            // Training Type Filter - Only for GTM and Admin
            SelectFilter::make('training_type')
                ->label('نوع التدريب')
                ->options([
                    Application::UNIVERSITY => Application::getTrainingTypeLabel(Application::UNIVERSITY),
                    Application::PRACTICE => Application::getTrainingTypeLabel(Application::PRACTICE),
                ])
                ->visible(fn() => $statusTitle === null && (
                    Auth::user()->isAdmin() ||
                    Auth::user()->isTrainingManagerLike() ||
                    Auth::user()->isHOA() ||
                    Auth::user()->isDepartment() ||
                    Auth::user()->isSectionHead()
                )),

            // Hierarchical Filter
            \Filament\Tables\Filters\Filter::make('hierarchy_filter')
                ->form([
                    Select::make('administrative_id')
                        ->label('الإدارة')
                        ->options(function () {
                            $query = Administrative::query();
                            if (!Auth::user()->isAdmin()) {
                                $query->active();
                            }
                            // For MOH and Medical Manager, only show administratives that have sections with medical departments
                            if (Auth::user()->isMinistry() || Auth::user()->isMedicalManager()) {
                                $query->whereHas('sections', fn($q) => $q->whereHas('departments', fn($d) => $d->where('is_medical', true)->when(!Auth::user()->isAdmin(), fn($s) => $s->active()->visible())));
                            }
                            if (Auth::user()->isAssistantTrainingManager()) {
                                $query->whereHas('sections.departments', fn($d) => $d->whereIn('departments.id', Auth::user()->managedDepartmentIds()));
                            }
                            return $query->pluck('name', 'id');
                        })
                        ->default(fn() => Auth::user()->isHOA() || Auth::user()->isMedicalManager() ? Auth::user()->administrative_id : null)
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('department_id', null);
                            $set('section_id', null);
                        })
                        ->visible(fn() => !(Auth::user()->isHOA() || Auth::user()->isSectionHead()) && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry() || Auth::user()->isMedicalManager() || Auth::user()->isDepartment())),

                    Select::make('department_id')
                        ->label('الدائرة')
                        ->multiple()
                        ->options(function (Get $get) {
                            $user = Auth::user();
                            if ($user->isMedicalManager()) {
                                $q = Department::where('is_medical', true);
                                if (!$user->isAdmin()) {
                                    $q->active()->visible();
                                }
                                return $q->pluck('name', 'id');
                            }

                            $query = Department::query();
                            if (!$user->isAdmin()) {
                                $query->active()->visible();
                            }
                            if ($adminId = $get('administrative_id')) {
                                $query->whereHas('sections', fn($q) => $q->where('administrative_id', $adminId)->when(!$user->isAdmin(), fn($s) => $s->active()));
                            }
                            if ($user->isAssistantTrainingManager()) {
                                $query->whereIn('id', $user->managedDepartmentIds());
                            }

                            return $query->pluck('name', 'id');
                        })
                        ->default(fn() => Auth::user()->isDepartment() ? [Auth::user()->department_id] : null)
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn(Set $set) => $set('section_id', null))
                        ->visible(fn() => !(Auth::user()->isDepartment() || Auth::user()->isSectionHead()) && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin() || Auth::user()->isHOA() || Auth::user()->isMedicalManager())),

                    Select::make('section_id')
                        ->label('القسم')
                        ->default(fn() => Auth::user()->isSectionHead() ? Auth::user()->section_id : null)
                        ->searchable()
                        ->preload()
                        ->options(function (Get $get) {
                            $user = Auth::user();
                            $query = Section::query();
                            if (!$user->isAdmin()) {
                                $query->active();
                            }

                            // Role-based restrictions
                            if ($user->isDepartment() && $user->department) {
                                $query->whereHas('departments', fn($q) => $q->where('departments.id', $user->department->id)->when(!$user->isAdmin(), fn($d) => $d->active()->visible()));
                            } elseif ($user->isHOA() && $user->administrative) {
                                $query->where('administrative_id', $user->administrative->id);
                            } elseif ($user->isMedicalManager() && $user->administrative) {
                                $query->whereHas('departments', fn($q) => $q->where('is_medical', true)->when(!$user->isAdmin(), fn($d) => $d->active()->visible()));
                            } elseif ($user->isMinistry()) {
                                $query->whereHas('departments', fn($q) => $q->where('is_medical', true)->when(!$user->isAdmin(), fn($d) => $d->active()->visible()));
                            } elseif ($user->isSectionHead() && $user->section) {
                                $query->where('id', $user->section->id);
                            } elseif ($user->isAssistantTrainingManager()) {
                                $query->whereHas('departments', fn($q) => $q->whereIn('departments.id', $user->managedDepartmentIds()));
                            }

                            // Dependent filtering - filter sections by selected department or administrative
                            if (! empty($deptIds = $get('department_id'))) {
                                $query->whereHas('departments', fn($q) => $q->whereIn('departments.id', (array) $deptIds)->when(!$user->isAdmin(), fn($d) => $d->active()->visible()));
                            } elseif (!$user->isMedicalManager() && ($adminId = $get('administrative_id'))) {
                                $query->where('administrative_id', $adminId);
                            }

                            return $query->pluck('name', 'id');
                        })
                        ->visible(fn() => !Auth::user()->isSectionHead()),

                    Select::make('institution_id')
                        ->label('المؤسسة التعليمية')
                        ->options(Institution::has('trainees')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        // ->afterStateUpdated(fn(Set $set) => $set('college_id', null))
                        ->visible(fn() => $statusTitle === null && (Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike())),

                    Select::make('major_id')
                        ->label('التخصص')
                        ->options(self::getMajorOptions())
                        ->searchable()
                        ->preload()
                        ->visible(fn() => Auth::user()->isCollegeSupervisor() || Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike()),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['administrative_id'] ?? null,
                            fn(Builder $query, $adminId) => $query->whereHas('section', fn($q) => $q->where('administrative_id', $adminId))
                        )
                        ->when(
                            $data['institution_id'] ?? null,
                            fn(Builder $query, $institutionId) => $query->where('institution_id', $institutionId)
                        )
                        ->when(
                            ! empty($data['department_id'] ?? null),
                            fn(Builder $query) => $query->whereHas(
                                'section',
                                fn($q) =>
                                $q->whereHas('departments', fn($d) => $d->whereIn('departments.id', (array) $data['department_id'])->visible())
                            )
                        )
                        ->when(
                            $data['section_id'] ?? null,
                            fn(Builder $query, $sectionId) => $query->where('section_id', $sectionId)
                        )
                        ->when(
                            $data['major_id'] ?? null,
                            fn(Builder $query, $majorId) => $query->where('major_id', $majorId)
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['administrative_id'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('الإدارة: ' . Administrative::find($data['administrative_id'])?->name)
                            ->removeField('administrative_id');
                    }
                    if ($data['institution_id'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('المؤسسة: ' . Institution::find($data['institution_id'])?->name)
                            ->removeField('institution_id');
                    }
                    foreach ((array) ($data['department_id'] ?? []) as $deptId) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('الدائرة: ' . Department::find($deptId)?->name)
                            ->removeField('department_id');
                    }
                    if ($data['section_id'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('القسم: ' . Section::find($data['section_id'])?->name)
                            ->removeField('section_id');
                    }
                    if ($data['major_id'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('التخصص: ' . Major::find($data['major_id'])?->name)
                            ->removeField('major_id');
                    }
                    return $indicators;
                }),

            // Date Range Filter - application submission date
            \Filament\Tables\Filters\Filter::make('date_range')
                ->label('تاريخ بداية التدريب')
                ->form([
                    DatePicker::make('training_start_date')
                        ->label('من تاريخ')
                        ->placeholder('اختر تاريخ البداية')
                        ->native(false)
                        ->closeOnDateSelection(),
                    DatePicker::make('training_end_date')
                        ->label('إلى تاريخ')
                        ->placeholder('اختر تاريخ النهاية')
                        ->native(false)
                        ->closeOnDateSelection(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['training_start_date'] ?? null,
                            fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                        )
                        ->when(
                            $data['training_end_date'] ?? null,
                            fn(Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['training_start_date'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('من تاريخ: ' . Carbon::parse($data['training_start_date'])->format('Y-m-d'))
                            ->removeField('training_start_date');
                    }
                    if ($data['training_end_date'] ?? null) {
                        $indicators[] = \Filament\Tables\Filters\Indicator::make('إلى تاريخ: ' . Carbon::parse($data['training_end_date'])->format('Y-m-d'))
                            ->removeField('training_end_date');
                    }
                    return $indicators;
                })
                ->visible(fn() => Auth::user()->isTrainingManagerLike()
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
                    Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike() ||
                        ((Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()) && in_array($record->status, [Application::STATUS_NEW, Application::STATUS_INITIAL_APPROVE, Application::STATUS_CONFIRMATION]))
                ),

            Action::make('initial_approve')
                ->label('موافقة مبدئية')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_NEW && (Auth::user()->isTrainingManagerLike() || Auth::user()->isMinistry()))
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
                ->color(function (Application $record) {
                    if (! $record->section_id) return 'success';
                    $section = Section::find($record->section_id);
                    if (! $section) return 'success';
                    $active = Application::where('section_id', $section->id)
                        ->where('status', Application::STATUS_STARTED_TRAINING)
                        ->count();
                    return $active >= $section->capacity ? 'danger' : 'success';
                })
                ->visible(fn(Application $record) => in_array($record->status, [Application::STATUS_CONFIRMATION, Application::STATUS_WAITING_LIST]) && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->form(fn(Application $record) => self::getProcessApplicationFormSchema($record))
                ->action(fn(Application $record, array $data) => self::processApplicationAction($record, $data)),

            Action::make('end_training')
                ->label('إنهاء التدريب')
                ->icon('heroicon-o-flag')
                ->color('warning')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_STARTED_TRAINING && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_ENDED_TRAINING, 'end_date' => now()]);
                    Notification::make()->title('تم إنهاء التدريب بنجاح')->success()->send();
                }),

            Action::make('cancel_training')
                ->label('إلغاء التدريب')
                ->icon('heroicon-o-stop-circle')
                ->color('danger')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_STARTED_TRAINING && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()))
                ->form([
                    Textarea::make('cancel_reason')
                        ->label('سبب الإلغاء')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (Application $record, array $data) {
                    if (!Auth::user()->can('cancel', $record)) {
                        Notification::make()->title('لا تملك صلاحية إلغاء هذا الطلب')->danger()->send();
                        return;
                    }

                    $daysNote = $record->days_note ?? [];
                    $daysNote['who_cancelled'] = Auth::id();
                    $daysNote['cancel_reason'] = $data['cancel_reason'] ?? null;

                    $record->update([
                        'status' => Application::STATUS_CANCELLED,
                        'days_note' => $daysNote,
                    ]);
                    Notification::make()->title('تم إلغاء طلب التدريب بنجاح')->success()->send();
                }),

            Action::make('reject')
                ->label('رفض الطلب')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn(Application $record) => in_array($record->status, [Application::STATUS_NEW, Application::STATUS_INITIAL_APPROVE, Application::STATUS_CONFIRMATION, Application::STATUS_WAITING_LIST]) && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_REJECTED]);
                    Notification::make()->title('تم رفض الطلب')->danger()->send();
                }),

            Action::make('restore_rejection')
                ->label('استعادة من الرفض')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn(Application $record) => $record->status === Application::STATUS_REJECTED && (Auth::user()->isTrainingManagerLike() || Auth::user()->isAdmin()))
                ->requiresConfirmation()
                ->action(function (Application $record) {
                    $record->update(['status' => Application::STATUS_NEW]);
                    Notification::make()->title('تمت استعادة الطلب إلى جديد')->success()->send();
                }),

            self::getUploadTraineeFilesAction(),

            self::getDownloadTraineeFilesAction(),

            /*
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
                    */
        ];
    }

    public static function getUploadTraineeFilesAction(): Action
    {
        return Action::make('upload_trainee_files')
            ->label('رفع ملفات الطلب')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->visible(fn(Application $record) =>
                (Auth::user()->isAdministrative() || Auth::user()->isAdmin()) &&
                $record->training_type === Application::UNIVERSITY &&
                $record->status === Application::STATUS_ENDED_TRAINING &&
                $record->getMedia('trainee_application_files')->isEmpty()
            )
            ->form([
                FileUpload::make('files')
                    ->label('ملفات الطلب (PDF أو صور)')
                    ->multiple()
                    ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg', 'image/jpg'])
                    ->required()
                    ->disk('local')
                    ->directory('temp-trainee-files')
                    ->maxSize(20480),
            ])
            ->action(function (Application $record, array $data) {
                $files = array_filter((array) ($data['files'] ?? []));
                if (empty($files)) {
                    Notification::make()->title('لم يتم رفع أي ملفات')->warning()->send();
                    return;
                }

                $zipName = self::makeApplicationFilesZipName($record);
                $tempZipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '_' . $zipName;

                $zip = new \ZipArchive();
                if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                    Notification::make()->title('فشل إنشاء الملف المضغوط')->danger()->send();
                    return;
                }

                $usedNames = [];
                foreach ($files as $filePath) {
                    $realPath = $filePath instanceof TemporaryUploadedFile
                        ? $filePath->getRealPath()
                        : Storage::disk('local')->path((string) $filePath);

                    $originalName = $filePath instanceof TemporaryUploadedFile
                        ? $filePath->getClientOriginalName()
                        : basename($realPath);

                    $finalName = $originalName;
                    $i = 1;
                    while (in_array($finalName, $usedNames)) {
                        $ext  = pathinfo($originalName, PATHINFO_EXTENSION);
                        $base = pathinfo($originalName, PATHINFO_FILENAME);
                        $finalName = "{$base}_{$i}.{$ext}";
                        $i++;
                    }
                    $usedNames[] = $finalName;

                    if (file_exists($realPath)) {
                        $zip->addFile($realPath, $finalName);
                    }
                }

                $zip->close();

                $record->addMedia($tempZipPath)
                    ->usingFileName($zipName)
                    ->toMediaCollection('trainee_application_files');

                foreach ($files as $filePath) {
                    if (is_string($filePath)) {
                        Storage::disk('local')->delete($filePath);
                    }
                }

                $collegeUser = $record->college?->user;
                if ($collegeUser) {
                    try {
                        $collegeUser->notify(new ApplicationFilesUploadedNotification($record));
                    } catch (\Throwable $e) {
                        Log::error('Failed to notify college supervisor on file upload: ' . $e->getMessage());
                    }
                }

                Notification::make()->title('تم رفع الملفات بنجاح')->success()->send();
            });
    }

    public static function getDownloadTraineeFilesAction(): Action
    {
        return Action::make('download_trainee_files')
            ->label('تحميل ملفات الطلب')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->visible(fn(Application $record) =>
                (Auth::user()->isCollegeSupervisor() || Auth::user()->isAdmin()) &&
                $record->training_type === Application::UNIVERSITY &&
                $record->status === Application::STATUS_ENDED_TRAINING &&
                $record->getMedia('trainee_application_files')->isNotEmpty()
            )
            ->url(fn(Application $record) => route('applications.download-trainee-files', ['application' => $record->id]))
            ->openUrlInNewTab();
    }

    public static function getProcessApplicationFormSchema(Application $record): array
    {
        $schema = [];
        $sectionStats = $record->section?->getCapacityStats();
        $isSectionInactive = ! ($record->section?->active ?? false);
        $isSectionFull = $sectionStats['is_full'] ?? false;

        if ($isSectionInactive) {
            $schema[] = Placeholder::make('warning_inactive')
                ->label('تنبيه: القسم المسجل غير نشط')
                ->content('القسم المرتبط بالطلب حالياً غير نشط. لا يمكن معالجة الطلب.')
                ->columnSpanFull()
                ->extraAttributes(['class' => 'text-danger-600 font-bold']);

            return $schema;
        }

        if ($isSectionFull) {
            $schema[] = Placeholder::make('warning_full')
                ->label(new \Illuminate\Support\HtmlString('<span style="font-size:1.15rem; font-weight:800; color:#b45309;">⚠️ تنبيه: القسم ممتلئ بالكامل</span>'))
                ->content(new \Illuminate\Support\HtmlString(self::getSectionFullWarningTemplate($record->section)))
                ->columnSpanFull();
        }

        $defaultStatus = match ($record->status) {
            Application::STATUS_WAITING_LIST => Application::STATUS_STARTED_TRAINING,
            Application::STATUS_CONFIRMATION => Application::STATUS_WAITING_LIST,
            default => Application::STATUS_STARTED_TRAINING,
        };

        if ($isSectionFull) {
            $statusOptions = [
                Application::STATUS_WAITING_LIST => Application::getStatusLabel(Application::STATUS_WAITING_LIST),
            ];
            $defaultStatus = Application::STATUS_WAITING_LIST;
        } else {
            $statusOptions = [
                Application::STATUS_STARTED_TRAINING => Application::getStatusLabel(Application::STATUS_STARTED_TRAINING),
                Application::STATUS_WAITING_LIST => Application::getStatusLabel(Application::STATUS_WAITING_LIST),
            ];
        }

        $schema[] = Select::make('new_status')
            ->label('الحالة الجديدة')
            ->options($statusOptions)
            ->default($defaultStatus)
            ->required()
            ->reactive();

        $computedDefaultDate = null;
        if ($isSectionFull && $record->section_id) {
            $earliestEndingApp = Application::where('section_id', $record->section_id)
                ->where('status', Application::STATUS_STARTED_TRAINING)
                ->whereNotNull('end_date')
                ->orderBy('end_date', 'asc')
                ->first();
            if ($earliestEndingApp) {
                $computedDefaultDate = Carbon::parse($earliestEndingApp->end_date)->addDay()->toDateString();
            }
        }
        $computedDefaultDate = $computedDefaultDate ?? now()->toDateString();

        $updateEndDate = function (Get $get, Set $set) use ($record, $computedDefaultDate) {
            $hours       = (int) $get('training_hours');
            $dailyHrs    = (int) $get('daily_hours');
            $days        = (array) $get('training_days');
            $days        = array_filter($days, fn($v) => $v !== '' && $v !== null);
            $daysPerWeek = count($days);
            $startDate   = $get('start_date') ?: $computedDefaultDate;

                        $auto = $record->calculateEndDate(
                            customDailyHours: (int) $get('daily_hours'),
                            customDays: (array) $get('training_days'),
                            customStartDate: $get('start_date'),
                            customTrainingHours: (int) $get('training_hours')
                        );
                        $set('end_date', $auto);
        };

        $schema[] = DatePicker::make('start_date')
            ->label('تاريخ بدء التدريب / المتوقع')
            ->required()
            ->visible(fn(Get $get) => in_array((int)$get('new_status'), [Application::STATUS_STARTED_TRAINING, Application::STATUS_WAITING_LIST]))
            ->default($computedDefaultDate)
            ->afterStateHydrated(fn($component, $state) => $component->state($state ?? $computedDefaultDate))
            ->minDate($isSectionFull ? $computedDefaultDate : null)
            ->displayFormat('Y/m/d')
            ->native(false)
            ->closeOnDateSelection()
            ->live()
            ->afterStateUpdated($updateEndDate);

        $isRelevantStatus = fn(Get $get) => in_array((int)$get('new_status'), [Application::STATUS_STARTED_TRAINING, Application::STATUS_WAITING_LIST]);

        $schema[] = TextInput::make('training_hours')
            ->label('إجمالي ساعات التدريب')
            ->numeric()
            ->required()
            ->minValue(1)
            ->suffix('ساعة')
            ->placeholder('مثال: 200')
            ->visible($isRelevantStatus)
            ->default(fn() => $record->training_hours ?? null)
            ->afterStateHydrated(fn($component, $state) => $component->state($state ?? $record->training_hours))
            ->live(debounce: 500)
            ->afterStateUpdated($updateEndDate)
            ->extraAttributes(['class' => 'font-bold']);

        $schema[] = CheckboxList::make('training_days')
            ->label('أيام التدريب الأسبوعية')
            ->options(Application::ALL_DAYS)
            ->columns(5)
            ->required()
            ->visible($isRelevantStatus)
            ->afterStateHydrated(fn($component, $state, Application $record) => $component->state($record->days_note['training_days'] ?? [
                Application::DAY_SUNDAY,
                Application::DAY_MONDAY,
                Application::DAY_TUESDAY,
                Application::DAY_WEDNESDAY,
                Application::DAY_THURSDAY,
            ]))
            ->live()
            ->afterStateUpdated($updateEndDate);

        $schema[] = TextInput::make('daily_hours')
            ->label('عدد ساعات التدريب في اليوم')
            ->numeric()
            ->required()
            ->minValue(1)
            ->suffix('ساعة/يوم')
            ->placeholder('مثال: 6')
            ->visible($isRelevantStatus)
            ->default(fn() => $record->days_note['daily_hours'] ?? 6)
            ->afterStateHydrated(fn($component, $state) => $component->state($state ?? ($record->days_note['daily_hours'] ?? 6)))
            ->live(debounce: 500)
            ->afterStateUpdated($updateEndDate);

        $schema[] = Placeholder::make('calc_summary')
            ->label('ملخص الحساب')
            ->content(function (Get $get) {
                $hours      = (int) $get('training_hours');
                $dailyHrs   = (int) $get('daily_hours');
                $days       = (array) $get('training_days');
                $days       = array_filter($days, fn($v) => $v !== '' && $v !== null);
                $daysPerWeek = count($days);

                if ($hours < 1 || $dailyHrs < 1) {
                    return 'أدخل إجمالي الساعات وساعات اليوم لرؤية الحساب.';
                }

                $sessionsNeeded = (int) ceil($hours / $dailyHrs);

                if ($daysPerWeek < 1) {
                    return "إجمالي جلسات التدريب: {$sessionsNeeded} يوم — يرجى اختيار الأيام لحساب المدة الكاملة.";
                }

                $calendarDays = (int) round(($sessionsNeeded / $daysPerWeek) * 7);
                $weeksNeeded  = round($calendarDays / 7, 1);

                return "🗓 جلسات مطلوبة: {$sessionsNeeded} يوم تدريب | أيام/أسبوع: {$daysPerWeek} | أسابيع: {$weeksNeeded} | المدة الإجمالية: {$calendarDays} يوم تقريباً";
            })
            ->visible($isRelevantStatus)
            ->columnSpanFull();

        $schema[] = DatePicker::make('end_date')
            ->label('تاريخ انتهاء التدريب المتوقع')
            ->required()
            ->visible($isRelevantStatus)
            ->afterStateHydrated(function ($component, $state) use ($record, $computedDefaultDate) {
                // Keep existing end_date if already set on the record
                if ($record->end_date) {
                    $component->state(Carbon::parse($record->end_date)->format('Y-m-d'));
                    return;
                }
                $auto = $record->calculateEndDate(
                    customDailyHours: (int) ($record->days_note['daily_hours'] ?? 8),
                    customDays: (array) ($record->days_note['training_days'] ?? []),
                    customStartDate: $computedDefaultDate,
                    customTrainingHours: (int) ($record->training_hours ?? 0)
                );
                
                $component->state($auto ?? Carbon::parse($computedDefaultDate)->addDays(30)->toDateString());
            })
            ->helperText(function (Get $get) use ($computedDefaultDate) {
                $hours      = (int) $get('training_hours');
                $dailyHrs   = (int) $get('daily_hours');
                $days       = (array) $get('training_days');
                $days       = array_filter($days, fn($v) => $v !== '' && $v !== null);
                $daysPerWeek = count($days);

                $startDate  = $get('start_date') ?: $computedDefaultDate;

                if ($hours > 0 && $dailyHrs > 0 && $daysPerWeek > 0 && $startDate) {
                    return "تم تحديث التاريخ أعلاه تلقائياً!";
                }
                return null;
            })
            ->displayFormat('Y/m/d')
            ->native(false)
            ->closeOnDateSelection()
            ->reactive();

        $schema[] = Textarea::make('note')
            ->label('ملاحظات')
            ->rows(3)
            ->visible($isRelevantStatus)
            ->formatStateUsing(fn($state, Application $record) => $record->days_note['note'] ?? null);

        return $schema;
    }

    public static function processApplicationAction(Application $record, array $data): void
    {
        if (! isset($data['new_status'])) {
            return;
        }

        $updateData = ['status' => $data['new_status']];

        if (in_array($data['new_status'], [Application::STATUS_STARTED_TRAINING, Application::STATUS_WAITING_LIST])) {
            $hours       = (int) ($data['training_hours'] ?? 0);
            $dailyHrs    = (int) ($data['daily_hours'] ?? 6);
            $selectedDays = array_filter((array) ($data['training_days'] ?? []), fn($v) => $v !== '' && $v !== null);
            $daysPerWeek  = count($selectedDays);

            // Compute end date using the central model method
            $endDate = $data['end_date'] ?? $record->calculateEndDate(
                customDailyHours: (int) ($data['daily_hours'] ?? 6),
                customDays: array_filter((array) ($data['training_days'] ?? []), fn($v) => $v !== '' && $v !== null)
            );

            $updateData['start_date']     = $data['start_date'];
            $updateData['end_date']       = $endDate;
            $updateData['training_hours'] = $hours ?: null;
            $updateData['days_note'] = [
                'training_days' => array_map('intval', $selectedDays),
                'daily_hours'   => $dailyHrs,
                'note'          => $data['note'] ?? null,
            ];
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
                        if ($user->isAdmin() || $user->isTrainingManagerLike()) {
                            $excluded = ['university_training', 'practice_training', 'rejected', 'finished'];

                            if ($user->isTrainingManagerLike()) {
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
                    ->modalSubmitAction(function ($action, $livewire) {
                        $records = $livewire->getSelectedTableRecords();
                        if ($records->isEmpty()) return $action;

                        $firstRecord = $records->first();
                        $allSame = $records->every(fn($r) => $r->section_id === $firstRecord->section_id);

                        if (!$allSame) {
                            return $action->disabled();
                        }

                        return $action;
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
                                $options = [
                                    Application::STATUS_WAITING_LIST => Application::getStatusLabel(Application::STATUS_WAITING_LIST),
                                    Application::STATUS_STARTED_TRAINING => Application::getStatusLabel(Application::STATUS_STARTED_TRAINING),
                                ];
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

                        $updateEndDate = function (Get $get, Set $set) {
                            $hours       = (int) $get('training_hours');
                            $dailyHrs    = (int) $get('daily_hours');
                            $days        = (array) $get('training_days');
                            $days        = array_filter($days, fn($v) => $v !== '' && $v !== null);
                            $daysPerWeek = count($days);
                            $startDate   = $get('start_date') ?: now()->toDateString();

                            $auto = (new Application())->calculateEndDate(
                                customDailyHours: (int) $get('daily_hours'),
                                customDays: (array) $get('training_days'),
                                customStartDate: $get('start_date'),
                                customTrainingHours: (int) $get('training_hours')
                            );
                            $set('end_date', $auto);
                        };

                        $isRelevantStatus = fn(Get $get) => in_array((int)$get('status'), [Application::STATUS_STARTED_TRAINING, Application::STATUS_WAITING_LIST]);

                        return [
                            Placeholder::make('warning_bulk_full')
                                ->label(function ($livewire) {
                                    $records = $livewire->getSelectedTableRecords();
                                    if ($records->isEmpty()) return null;

                                    $firstRecord = $records->first();
                                    $allSame = $records->every(fn($r) => $r->section_id === $firstRecord->section_id);

                                    $text = $allSame ? 'القسم ممتلئ بالكامل' : 'أقسام مختلفة';
                                    $color = $allSame ? '#b45309' : '#dc2626';

                                    return new HtmlString("<span style=\"font-size:1.15rem; font-weight:800; color:{$color};\">⚠️ تنبيه: {$text}</span>");
                                })
                                ->content(function ($livewire) {
                                    $records = $livewire->getSelectedTableRecords();
                                    if ($records->isEmpty()) return null;

                                    $firstRecord = $records->first();
                                    $section = $firstRecord->section;
                                    if (!$section) return null;

                                    $allSame = $records->every(fn($r) => $r->section_id === $section->id);
                                    if (!$allSame) {
                                        return new HtmlString(
                                            '<div style="background:#fee2e2; border:2px solid #ef4444; border-radius:0.5rem; padding:0.85rem 1.1rem; font-size:1rem; font-weight:700; color:#b91c1c; line-height:1.8;">'
                                            . '⚠️ تنبيه: الطلبات المحددة تنتمي لأقسام مختلفة.'
                                            . '</div>'
                                        );
                                    }

                                    if (!($section->getCapacityStats()['is_full'] ?? false)) return null;

                                    return new HtmlString(self::getSectionFullWarningTemplate($section));
                                })
                                ->visible(function ($livewire) {
                                    $records = $livewire->getSelectedTableRecords();
                                    if ($records->isEmpty()) return false;

                                    $firstRecord = $records->first();
                                    $section = $firstRecord->section;
                                    if (!$section) return false;

                                    $allSame = $records->every(fn($r) => $r->section_id === $section->id);
                                    if (!$allSame) return true;

                                    return $section->getCapacityStats()['is_full'] ?? false;
                                })
                                ->columnSpanFull(),

                            Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options($options)
                                ->default(fn() => (is_array($options) && count($options) === 1) ? array_key_first($options) : null)
                                ->required()
                                ->reactive(),

                            DatePicker::make('start_date')
                                ->label('تاريخ بدء التدريب')
                                ->visible($isRelevantStatus)
                                ->required()
                                ->default(now()->toDateString())
                                ->live()
                                ->afterStateUpdated($updateEndDate),

                            TextInput::make('training_hours')
                                ->label('إجمالي ساعات التدريب')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->suffix('ساعة')
                                ->placeholder('مثال: 200')
                                ->visible($isRelevantStatus)
                                ->live(debounce: 500)
                                ->afterStateUpdated($updateEndDate)
                                ->extraAttributes(['class' => 'font-bold']),

                            CheckboxList::make('training_days')
                                ->label('أيام التدريب الأسبوعية')
                                ->options(Application::ALL_DAYS)
                                ->columns(5)
                                ->required()
                                ->visible($isRelevantStatus)
                                ->default([
                                    Application::DAY_SUNDAY,
                                    Application::DAY_MONDAY,
                                    Application::DAY_TUESDAY,
                                    Application::DAY_WEDNESDAY,
                                    Application::DAY_THURSDAY,
                                ])
                                ->live()
                                ->afterStateUpdated($updateEndDate),

                            TextInput::make('daily_hours')
                                ->label('عدد ساعات التدريب في اليوم')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->suffix('ساعة/يوم')
                                ->placeholder('مثال: 6')
                                ->visible($isRelevantStatus)
                                ->default(6)
                                ->live(debounce: 500)
                                ->afterStateUpdated($updateEndDate),

                            Placeholder::make('calc_summary')
                                ->label('ملخص الحساب')
                                ->content(function (Get $get) {
                                    $hours      = (int) $get('training_hours');
                                    $dailyHrs   = (int) $get('daily_hours');
                                    $days       = (array) $get('training_days');
                                    $days       = array_filter($days, fn($v) => $v !== '' && $v !== null);
                                    $daysPerWeek = count($days);

                                    if ($hours < 1 || $dailyHrs < 1) {
                                        return 'أدخل إجمالي الساعات وساعات اليوم لرؤية الحساب.';
                                    }

                                    $sessionsNeeded = (int) ceil($hours / $dailyHrs);

                                    if ($daysPerWeek < 1) {
                                        return "إجمالي جلسات التدريب: {$sessionsNeeded} يوم — يرجى اختيار الأيام لحساب المدة الكاملة.";
                                    }

                                    $calendarDays = (int) round(($sessionsNeeded / $daysPerWeek) * 7);
                                    $weeksNeeded  = round($calendarDays / 7, 1);

                                    return "🗓 جلسات مطلوبة: {$sessionsNeeded} يوم تدريب | أيام/أسبوع: {$daysPerWeek} | أسابيع: {$weeksNeeded} | المدة الإجمالية: {$calendarDays} يوم تقريباً";
                                })
                                ->visible($isRelevantStatus)
                                ->columnSpanFull(),

                            DatePicker::make('end_date')
                                ->label('تاريخ انتهاء التدريب المتوقع')
                                ->required()
                                ->visible($isRelevantStatus)
                                ->default(function () {
                                    return now()->addDays(30)->toDateString();
                                })
                                ->helperText(function (Get $get) {
                                    $hours      = (int) $get('training_hours');
                                    $dailyHrs   = (int) $get('daily_hours');
                                    $days       = (array) $get('training_days');
                                    $days       = array_filter($days, fn($v) => $v !== '' && $v !== null);
                                    $daysPerWeek = count($days);
                                    $startDate  = $get('start_date') ?: now()->toDateString();
                                    if ($hours > 0 && $dailyHrs > 0 && $daysPerWeek > 0 && $startDate) {
                                        return "تم تحديث التاريخ أعلاه تلقائياً!";
                                    }
                                    return null;
                                })
                                ->displayFormat('Y/m/d')
                                ->native(false)
                                ->closeOnDateSelection()
                                ->reactive(),

                            Textarea::make('note')
                                ->label('ملاحظات')
                                ->rows(3)
                                ->visible($isRelevantStatus),
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

                            if ($user->isAdmin() || $user->isTrainingManagerLike()) {
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

                            if (in_array($status, [Application::STATUS_STARTED_TRAINING, Application::STATUS_WAITING_LIST])) {
                                $hours       = (int) ($data['training_hours'] ?? 0);
                                $dailyHrs    = (int) ($data['daily_hours'] ?? 6);
                                $selectedDays = array_filter((array) ($data['training_days'] ?? []), fn($v) => $v !== '' && $v !== null);
                                $daysPerWeek  = count($selectedDays);

                                $endDate = $data['end_date'] ?? $record->calculateEndDate(
                                    customDailyHours: (int) ($data['daily_hours'] ?? 6),
                                    customDays: (array) ($data['training_days'] ?? []),
                                    customStartDate: $data['start_date'],
                                    customTrainingHours: (int) ($data['training_hours'] ?? 0)
                                );

                                $updateData['start_date']     = $data['start_date'];
                                $updateData['end_date']       = $endDate;
                                $updateData['training_hours'] = $hours ?: null;
                                $updateData['days_note'] = [
                                    'training_days' => array_map('intval', $selectedDays),
                                    'daily_hours'   => $dailyHrs,
                                    'note'          => $data['note'] ?? null,
                                ];
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

    protected static function getSectionFullWarningTemplate(Section $section): string
    {
        $stats = $section->getCapacityStats();

        $earliestEndingApp = Application::where('section_id', $section->id)
            ->where('status', Application::STATUS_STARTED_TRAINING)
            ->whereNotNull('end_date')
            ->orderBy('end_date', 'asc')
            ->first();

        $expectedStartDate = $earliestEndingApp
            ? Carbon::parse($earliestEndingApp->end_date)->addDay()->format('Y-m-d')
            : null;

        $warningContent = "القسم ممتلئ (السعة: {$stats['total']}). يمكنك فقط نقل الطلب إلى قائمة الانتظار.";
        if ($expectedStartDate) {
            $warningContent .= "\nتاريخ البدء المتوقع (بعد انتهاء أقرب متدرب): {$expectedStartDate}";
        }

        return '<div style="background:#fef3c7; border:2px solid #f59e0b; border-radius:0.5rem; padding:0.85rem 1.1rem; font-size:1rem; font-weight:700; color:#92400e; line-height:1.8;">'
            . nl2br(e($warningContent))
            . '</div>';
    }

    protected static function makeApplicationFilesZipName(Application $record): string
    {
        $traineeName = trim((string) ($record->trainee?->full_name ?? 'trainee'));
        $safeTraineeName = preg_replace('/[\\\\\\/:\*\?"<>\|]+/u', '-', $traineeName) ?? 'trainee';
        $safeTraineeName = preg_replace('/\s+/u', '_', trim($safeTraineeName)) ?? 'trainee';
        $safeTraineeName = trim($safeTraineeName, " ._-");

        if ($safeTraineeName === '') {
            $safeTraineeName = 'trainee';
        }

        return now()->format('Y-m-d') . '-' . $safeTraineeName . '.zip';
    }
}
