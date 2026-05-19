<?php

namespace App\Services\AiSearch;

use App\Enums\Gender;
use App\Models\Administrative;
use App\Models\Application;
use App\Models\College;
use App\Models\Department;
use App\Models\Governorate;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Section;
use App\Models\User;
use App\Support\AiSearch\AiTableSearchResult;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

use function Laravel\Ai\agent;

class AiTableSearchInterpreter
{
    private const RESOURCE_APPLICATIONS = 'applications';
    private const RESOURCE_TRAINEES = 'trainees';

    private const APPLICATION_STATUS_TOKENS = [
        'new' => Application::STATUS_NEW,
        'initial_approve' => Application::STATUS_INITIAL_APPROVE,
        'confirmation' => Application::STATUS_CONFIRMATION,
        'waiting_list' => Application::STATUS_WAITING_LIST,
        'started_training' => Application::STATUS_STARTED_TRAINING,
        'ended_training' => Application::STATUS_ENDED_TRAINING,
        'rejected' => Application::STATUS_REJECTED,
        'dropped' => Application::STATUS_DROPPED,
        'unknown' => Application::STATUS_UNKNOWN,
        'cancelled' => Application::STATUS_CANCELLED,
    ];

    private const TRAINING_TYPE_TOKENS = [
        'university' => Application::UNIVERSITY,
        'practice' => Application::PRACTICE,
    ];

    private const APPLICATION_TAB_TO_STATUS = [
        'new' => Application::STATUS_NEW,
        'initial_approve' => Application::STATUS_INITIAL_APPROVE,
        'confirmed' => Application::STATUS_CONFIRMATION,
        'training' => Application::STATUS_STARTED_TRAINING,
        'finished' => Application::STATUS_ENDED_TRAINING,
        'cancelled' => Application::STATUS_CANCELLED,
        'rejected' => Application::STATUS_REJECTED,
        'waiting_list' => Application::STATUS_WAITING_LIST,
    ];

    public function interpret(string $resource, string $rawQuery, User $user, array $context = []): AiTableSearchResult
    {
        $resource = Str::lower(trim($resource));
        $rawQuery = Str::squish($rawQuery);

        if (! in_array($resource, [self::RESOURCE_APPLICATIONS, self::RESOURCE_TRAINEES], true)) {
            return AiTableSearchResult::failure($resource, $rawQuery, 'Unsupported AI search resource.');
        }

        if ($rawQuery === '') {
            return AiTableSearchResult::failure($resource, $rawQuery, 'يرجى إدخال وصف واضح للبحث.');
        }

        try {
            $response = agent(
                instructions: $this->buildInstructions($resource, $user, $context),
                schema: fn (JsonSchema $schema): array => $this->buildSchema($resource, $schema),
            )->prompt($this->buildPrompt($resource, $rawQuery, $user, $context));
        } catch (RateLimitedException $e) {
            return AiTableSearchResult::failure($resource, $rawQuery, 'البحث الذكي مشغول حالياً، انتظر لحظة وأعد المحاولة.');
        } catch (Throwable $throwable) {
            report($throwable);

            return AiTableSearchResult::failure($resource, $rawQuery, 'تعذر تفسير طلب البحث حالياً.');
        }

        if (! $response instanceof StructuredAgentResponse) {
            return AiTableSearchResult::failure($resource, $rawQuery, 'تعذر تفسير طلب البحث حالياً.');
        }

        return match ($resource) {
            self::RESOURCE_APPLICATIONS => $this->normalizeApplicationsResult($rawQuery, $response->toArray(), $context),
            self::RESOURCE_TRAINEES => $this->normalizeTraineesResult($rawQuery, $response->toArray()),
            default => AiTableSearchResult::failure($resource, $rawQuery, 'Unsupported AI search resource.'),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function applyToQuery(string $resource, Builder $query, array $filters, ?string $keyword = null, ?User $user = null): Builder
    {
        return match ($resource) {
            self::RESOURCE_APPLICATIONS => $this->applyApplicationsFilters($query, $filters, $keyword),
            self::RESOURCE_TRAINEES => $this->applyTraineesFilters($query, $filters, $keyword, $user),
            default => $query,
        };
    }

    private function buildInstructions(string $resource, User $user, array $context): string
    {
        $role = User::ROLE_LABELS[$user->role] ?? (string) $user->role;
        $activeTab = $resource === self::RESOURCE_APPLICATIONS ? ($context['active_tab'] ?? 'all') : null;

        $resourceInstructions = match ($resource) {
            self::RESOURCE_APPLICATIONS => <<<'TXT'
Resource: applications
Allowed fields:
- status_in: array of statuses — use one value for single status, multiple values when user says "أو/or" between statuses. Values: new, initial_approve, confirmation, waiting_list, started_training, ended_training, rejected, cancelled, dropped, unknown
- training_type: one of university, practice
- trainee_name
- trainee_national_id
- university_number: university registration number of the trainee
- administrative_name: core name only, strip prefixes like إدارة/مديرية
- department_names: core names only, strip prefixes like دائرة/قسم (e.g. "دائرة التمريض" → "التمريض")
- section_name: core name only, strip prefixes like قسم/وحدة
- institution_name
- college_name
- major_name
- date_field: one of start_date, end_date, created_at
- date_from
- date_to
- trainee_street: partial street or neighborhood name from the trainee's address
- trainee_gender: one of male, female — gender of the trainee linked to the application
- trainee_governorate_name: governorate name of the trainee (not the section/department)
- training_hours_min: minimum training hours (integer)
- training_hours_max: maximum training hours (integer)
- keyword

Map Arabic and English status/training phrases to the allowed tokens above.
Use keyword only for broad searchable terms that do not clearly belong to a specific allowed field.
If the request is ambiguous, unsupported, or would require guessing, set is_supported=false.
TXT,
            self::RESOURCE_TRAINEES => <<<'TXT'
Resource: trainees
Allowed fields:
- full_name
- national_id
- phone_number
- gender: one of male, female
- governorate_name: trainee's governorate
- street: partial street/neighborhood name (free text)
- age_from: minimum age in years (integer)
- age_to: maximum age in years (integer)
- application_status_in: array of statuses — use one value for single status, multiple values when user says "أو/or". Values: new, initial_approve, confirmation, waiting_list, started_training, ended_training, rejected, cancelled, dropped, unknown
- training_type: one of university, practice
- administrative_name: core name only, strip prefixes like إدارة/مديرية
- department_names: core names only, strip prefixes like دائرة/قسم (e.g. "دائرة التمريض" → "التمريض")
- section_name: core name only, strip prefixes like قسم/وحدة
- keyword

You may use related application context only through the allowed related fields above.
Use keyword only for broad searchable terms that do not clearly belong to a specific allowed field.
If the request is ambiguous, unsupported, or would require guessing, set is_supported=false.
TXT,
            default => '',
        };

        $now              = now();
        $today            = $now->toDateString();
        $yesterday        = $now->copy()->subDay()->toDateString();
        $weekStart        = $now->copy()->startOfWeek()->toDateString();
        $weekEnd          = $now->copy()->endOfWeek()->toDateString();
        $monthStart       = $now->copy()->startOfMonth()->toDateString();
        $monthEnd         = $now->copy()->endOfMonth()->toDateString();
        $lastMonthStart   = $now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $lastMonthEnd     = $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();
        $yearStart        = $now->copy()->startOfYear()->toDateString();
        $yearEnd          = $now->copy()->endOfYear()->toDateString();
        $lastYearStart    = $now->copy()->subYear()->startOfYear()->toDateString();
        $lastYearEnd      = $now->copy()->subYear()->endOfYear()->toDateString();
        $q1Start          = $now->copy()->startOfYear()->toDateString();
        $q1End            = $now->copy()->startOfYear()->addMonths(2)->endOfMonth()->toDateString();
        $q2Start          = $now->copy()->startOfYear()->addMonths(3)->toDateString();
        $q2End            = $now->copy()->startOfYear()->addMonths(5)->endOfMonth()->toDateString();
        $q3Start          = $now->copy()->startOfYear()->addMonths(6)->toDateString();
        $q3End            = $now->copy()->startOfYear()->addMonths(8)->endOfMonth()->toDateString();
        $q4Start          = $now->copy()->startOfYear()->addMonths(9)->toDateString();
        $q4End            = $now->copy()->endOfYear()->toDateString();

        $tabLine = $activeTab !== null ? "- Current applications tab: {$activeTab}" : '';

        return <<<TXT
You translate natural-language table search requests into a safe structured filter payload.

Rules:
- User role: {$role}
{$tabLine}
- Today: {$today}
- Yesterday: {$yesterday}
- This week: {$weekStart} to {$weekEnd}
- This month: {$monthStart} to {$monthEnd}
- Last month: {$lastMonthStart} to {$lastMonthEnd}
- This year: {$yearStart} to {$yearEnd}
- Last year: {$lastYearStart} to {$lastYearEnd}
- Q1 (الربع الأول): {$q1Start} to {$q1End}
- Q2 (الربع الثاني): {$q2Start} to {$q2End}
- Q3 (الربع الثالث): {$q3Start} to {$q3End}
- Q4 (الربع الرابع): {$q4Start} to {$q4End}
- Use these exact dates when the user says "هذا الشهر", "الشهر الماضي", "هذا الأسبوع", "هذه السنة", "اليوم", "أمس", "الربع الأول/الثاني/الثالث/الرابع", or English equivalents.
- Arabic-first, English allowed.
- Return only structured data matching the schema.
- Never invent IDs, SQL, operators, or unsupported fields.
- Prefer exact mapping over broad guesses.
- If names are unclear, use the exact term the user wrote.
- If the request asks for something unsupported, set is_supported=false and explain briefly in Arabic.

{$resourceInstructions}
TXT;
    }

    private function buildPrompt(string $resource, string $rawQuery, User $user, array $context): string
    {
        $role = User::ROLE_LABELS[$user->role] ?? (string) $user->role;
        $activeTab = $resource === self::RESOURCE_APPLICATIONS ? ($context['active_tab'] ?? 'all') : null;

        $tabLine = $activeTab !== null ? "\nactive_tab: {$activeTab}" : '';

        return <<<TXT
resource: {$resource}
user_role: {$role}{$tabLine}
request: {$rawQuery}
TXT;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSchema(string $resource, JsonSchema $schema): array
    {
        $fields = [
            'is_supported' => $schema->boolean()->required(),
            'reason' => $schema->string()->max(200)->nullable()->required(),
            'keyword' => $schema->string()->max(100)->nullable()->required(),
        ];

        return match ($resource) {
            self::RESOURCE_APPLICATIONS => [
                ...$fields,
                'status_in' => $schema->array()->items($schema->string()->enum(array_keys(self::APPLICATION_STATUS_TOKENS)))->max(10)->nullable()->required(),
                'training_type' => $schema->string()->enum(array_keys(self::TRAINING_TYPE_TOKENS))->nullable()->required(),
                'trainee_name' => $schema->string()->max(120)->nullable()->required(),
                'trainee_national_id' => $schema->string()->max(20)->nullable()->required(),
                'university_number' => $schema->string()->max(50)->nullable()->required(),
                'administrative_name' => $schema->string()->max(120)->nullable()->required(),
                'department_names' => $schema->array()->items($schema->string()->max(120))->max(5)->nullable()->required(),
                'section_name' => $schema->string()->max(120)->nullable()->required(),
                'institution_name' => $schema->string()->max(120)->nullable()->required(),
                'college_name' => $schema->string()->max(120)->nullable()->required(),
                'major_name' => $schema->string()->max(120)->nullable()->required(),
                'date_field' => $schema->string()->enum(['start_date', 'end_date', 'created_at'])->nullable()->required(),
                'date_from' => $schema->string()->format('date')->nullable()->required(),
                'date_to' => $schema->string()->format('date')->nullable()->required(),
                'trainee_gender' => $schema->string()->enum(['male', 'female'])->nullable()->required(),
                'trainee_governorate_name' => $schema->string()->max(120)->nullable()->required(),
                'trainee_street' => $schema->string()->max(120)->nullable()->required(),
                'training_hours_min' => $schema->integer()->nullable()->required(),
                'training_hours_max' => $schema->integer()->nullable()->required(),
            ],
            self::RESOURCE_TRAINEES => [
                ...$fields,
                'full_name' => $schema->string()->max(120)->nullable()->required(),
                'national_id' => $schema->string()->max(20)->nullable()->required(),
                'phone_number' => $schema->string()->max(30)->nullable()->required(),
                'gender' => $schema->string()->enum(['male', 'female'])->nullable()->required(),
                'governorate_name' => $schema->string()->max(120)->nullable()->required(),
                'street' => $schema->string()->max(120)->nullable()->required(),
                'age_from' => $schema->integer()->nullable()->required(),
                'age_to' => $schema->integer()->nullable()->required(),
                'application_status_in' => $schema->array()->items($schema->string()->enum(array_keys(self::APPLICATION_STATUS_TOKENS)))->max(10)->nullable()->required(),
                'training_type' => $schema->string()->enum(array_keys(self::TRAINING_TYPE_TOKENS))->nullable()->required(),
                'administrative_name' => $schema->string()->max(120)->nullable()->required(),
                'department_names' => $schema->array()->items($schema->string()->max(120))->max(5)->nullable()->required(),
                'section_name' => $schema->string()->max(120)->nullable()->required(),
            ],
            default => $fields,
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function normalizeApplicationsResult(string $rawQuery, array $payload, array $context): AiTableSearchResult
    {
        if (! Arr::get($payload, 'is_supported', false)) {
            return AiTableSearchResult::failure(
                self::RESOURCE_APPLICATIONS,
                $rawQuery,
                $this->normalizeFailureReason($payload['reason'] ?? null),
            );
        }

        $filters = [];
        $summary = [];

        $statusTokens = is_array($payload['status_in'] ?? null)
            ? array_filter(array_map(fn ($t) => is_string($t) ? Str::squish($t) : null, $payload['status_in']))
            : [];
        $statusTokens = array_values(array_intersect_key(
            self::APPLICATION_STATUS_TOKENS,
            array_flip($statusTokens),
        ));

        if ($statusTokens !== []) {
            $filters['status_ids'] = $statusTokens;
        }

        if ($tabError = $this->validateApplicationsTab($filters['status_ids'] ?? null, $context['active_tab'] ?? null)) {
            return AiTableSearchResult::failure(self::RESOURCE_APPLICATIONS, $rawQuery, $tabError);
        }

        if (isset($filters['status_ids'])) {
            $labels = array_map(fn (int $s) => Application::getStatusLabel($s), $filters['status_ids']);
            $summary[] = 'الحالة: ' . implode(' أو ', $labels);
        }

        if ($trainingTypeToken = $this->stringValue($payload, 'training_type')) {
            $filters['training_type'] = self::TRAINING_TYPE_TOKENS[$trainingTypeToken];
            $summary[] = 'نوع التدريب: ' . Application::getTrainingTypeLabel($filters['training_type']);
        }

        if ($traineeName = $this->stringValue($payload, 'trainee_name')) {
            $filters['trainee_name'] = $traineeName;
            $summary[] = 'الاسم: ' . $traineeName;
        }

        if ($nationalId = $this->stringValue($payload, 'trainee_national_id')) {
            $filters['trainee_national_id'] = $nationalId;
            $summary[] = 'رقم الهوية: ' . $nationalId;
        }

        if ($universityNumber = $this->stringValue($payload, 'university_number')) {
            $filters['university_number'] = $universityNumber;
            $summary[] = 'رقم الجامعة: ' . $universityNumber;
        }

        if ($resolved = $this->resolveNamedModel(Administrative::query(), $payload['administrative_name'] ?? null, 'الإدارة')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_APPLICATIONS, $rawQuery, $resolved['error']);
            }

            $filters['administrative_id'] = $resolved['id'];
            $summary[] = 'الإدارة: ' . $resolved['label'];
        }

        if ($resolved = $this->resolveNamedModels(Department::query(), $payload['department_names'] ?? null, 'الدائرة')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_APPLICATIONS, $rawQuery, $resolved['error']);
            }

            if ($resolved['ids']) {
                $filters['department_ids'] = $resolved['ids'];
                $summary[] = 'الدوائر: ' . implode('، ', $resolved['labels']);
            }
        }

        if ($resolved = $this->resolveNamedModel(Section::query(), $payload['section_name'] ?? null, 'القسم')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_APPLICATIONS, $rawQuery, $resolved['error']);
            }

            $filters['section_id'] = $resolved['id'];
            $summary[] = 'القسم: ' . $resolved['label'];
        }

        if ($resolved = $this->resolveNamedModel(Institution::query(), $payload['institution_name'] ?? null, 'المؤسسة التعليمية')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_APPLICATIONS, $rawQuery, $resolved['error']);
            }

            $filters['institution_id'] = $resolved['id'];
            $summary[] = 'المؤسسة: ' . $resolved['label'];
        }

        if ($resolved = $this->resolveNamedModel(College::query(), $payload['college_name'] ?? null, 'الكلية')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_APPLICATIONS, $rawQuery, $resolved['error']);
            }

            $filters['college_id'] = $resolved['id'];
            $summary[] = 'الكلية: ' . $resolved['label'];
        }

        if ($resolved = $this->resolveNamedModel(Major::query(), $payload['major_name'] ?? null, 'التخصص')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_APPLICATIONS, $rawQuery, $resolved['error']);
            }

            $filters['major_id'] = $resolved['id'];
            $summary[] = 'التخصص: ' . $resolved['label'];
        }

        $dateFrom = $this->stringValue($payload, 'date_from');
        $dateTo = $this->stringValue($payload, 'date_to');

        if ($dateFrom || $dateTo) {
            $filters['date_field'] = $this->stringValue($payload, 'date_field') ?: 'start_date';
            $filters['date_from'] = $dateFrom;
            $filters['date_to'] = $dateTo;

            $dateFieldLabel = match ($filters['date_field']) {
                'end_date' => 'تاريخ نهاية التدريب',
                'created_at' => 'تاريخ الطلب',
                default => 'تاريخ بداية التدريب',
            };

            if ($dateFrom && $dateTo) {
                $summary[] = "{$dateFieldLabel}: من {$dateFrom} إلى {$dateTo}";
            } elseif ($dateFrom) {
                $summary[] = "{$dateFieldLabel}: من {$dateFrom}";
            } elseif ($dateTo) {
                $summary[] = "{$dateFieldLabel}: إلى {$dateTo}";
            }
        }

        if ($traineeStreet = $this->stringValue($payload, 'trainee_street')) {
            $filters['trainee_street'] = $traineeStreet;
            $summary[] = 'شارع المتدرب: ' . $traineeStreet;
        }

        if ($traineeGenderToken = $this->stringValue($payload, 'trainee_gender')) {
            $filters['trainee_gender'] = $traineeGenderToken === 'female' ? Gender::FEMALE->value : Gender::MALE->value;
            $summary[] = 'جنس المتدرب: ' . Gender::from($filters['trainee_gender'])->getLabel();
        }

        if ($resolved = $this->resolveNamedModel(Governorate::query(), $payload['trainee_governorate_name'] ?? null, 'محافظة المتدرب')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_APPLICATIONS, $rawQuery, $resolved['error']);
            }

            $filters['trainee_governorate_id'] = $resolved['id'];
            $summary[] = 'محافظة المتدرب: ' . $resolved['label'];
        }

        $trainingHoursMin = isset($payload['training_hours_min']) && is_numeric($payload['training_hours_min']) ? (int) $payload['training_hours_min'] : null;
        $trainingHoursMax = isset($payload['training_hours_max']) && is_numeric($payload['training_hours_max']) ? (int) $payload['training_hours_max'] : null;

        if ($trainingHoursMin !== null) {
            $filters['training_hours_min'] = $trainingHoursMin;
        }
        if ($trainingHoursMax !== null) {
            $filters['training_hours_max'] = $trainingHoursMax;
        }
        if ($trainingHoursMin !== null || $trainingHoursMax !== null) {
            if ($trainingHoursMin !== null && $trainingHoursMax !== null) {
                $summary[] = "ساعات التدريب: من {$trainingHoursMin} إلى {$trainingHoursMax}";
            } elseif ($trainingHoursMin !== null) {
                $summary[] = "ساعات التدريب: أكتر من {$trainingHoursMin}";
            } else {
                $summary[] = "ساعات التدريب: أقل من {$trainingHoursMax}";
            }
        }

        $keyword = $this->stringValue($payload, 'keyword');

        if ($keyword) {
            $summary[] = 'كلمة مفتاحية: ' . $keyword;
        }

        if (($filters === []) && blank($keyword)) {
            return AiTableSearchResult::failure(self::RESOURCE_APPLICATIONS, $rawQuery, 'الطلب غير واضح بما يكفي لتطبيق بحث ذكي آمن.');
        }

        return AiTableSearchResult::success(
            resource: self::RESOURCE_APPLICATIONS,
            rawQuery: $rawQuery,
            filters: $filters,
            summary: implode('، ', $summary),
            keyword: $keyword,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function normalizeTraineesResult(string $rawQuery, array $payload): AiTableSearchResult
    {
        if (! Arr::get($payload, 'is_supported', false)) {
            return AiTableSearchResult::failure(
                self::RESOURCE_TRAINEES,
                $rawQuery,
                $this->normalizeFailureReason($payload['reason'] ?? null),
            );
        }

        $filters = [];
        $summary = [];

        if ($fullName = $this->stringValue($payload, 'full_name')) {
            $filters['full_name'] = $fullName;
            $summary[] = 'الاسم: ' . $fullName;
        }

        if ($nationalId = $this->stringValue($payload, 'national_id')) {
            $filters['national_id'] = $nationalId;
            $summary[] = 'رقم الهوية: ' . $nationalId;
        }

        if ($phoneNumber = $this->stringValue($payload, 'phone_number')) {
            $filters['phone_number'] = $phoneNumber;
            $summary[] = 'رقم الهاتف: ' . $phoneNumber;
        }

        if ($genderToken = $this->stringValue($payload, 'gender')) {
            $filters['gender'] = $genderToken === 'female' ? Gender::FEMALE->value : Gender::MALE->value;
            $summary[] = 'الجنس: ' . Gender::from($filters['gender'])->getLabel();
        }

        if ($street = $this->stringValue($payload, 'street')) {
            $filters['street'] = $street;
            $summary[] = 'العنوان: ' . $street;
        }

        $ageFrom = isset($payload['age_from']) && is_numeric($payload['age_from']) ? (int) $payload['age_from'] : null;
        $ageTo   = isset($payload['age_to'])   && is_numeric($payload['age_to'])   ? (int) $payload['age_to']   : null;

        if ($ageFrom !== null) {
            $filters['age_from'] = $ageFrom;
        }
        if ($ageTo !== null) {
            $filters['age_to'] = $ageTo;
        }
        if ($ageFrom !== null || $ageTo !== null) {
            if ($ageFrom !== null && $ageTo !== null) {
                $summary[] = "العمر: من {$ageFrom} إلى {$ageTo} سنة";
            } elseif ($ageFrom !== null) {
                $summary[] = "العمر: أكبر من {$ageFrom} سنة";
            } else {
                $summary[] = "العمر: أصغر من {$ageTo} سنة";
            }
        }

        if ($resolved = $this->resolveNamedModel(Governorate::query(), $payload['governorate_name'] ?? null, 'المحافظة')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_TRAINEES, $rawQuery, $resolved['error']);
            }

            $filters['governorate_id'] = $resolved['id'];
            $summary[] = 'المحافظة: ' . $resolved['label'];
        }

        $appStatusTokens = is_array($payload['application_status_in'] ?? null)
            ? array_filter(array_map(fn ($t) => is_string($t) ? Str::squish($t) : null, $payload['application_status_in']))
            : [];
        $appStatusTokens = array_values(array_intersect_key(
            self::APPLICATION_STATUS_TOKENS,
            array_flip($appStatusTokens),
        ));

        if ($appStatusTokens !== []) {
            $filters['application_status_ids'] = $appStatusTokens;
            $labels = array_map(fn (int $s) => Application::getStatusLabel($s), $appStatusTokens);
            $summary[] = 'حالة الطلب: ' . implode(' أو ', $labels);
        }

        if ($trainingTypeToken = $this->stringValue($payload, 'training_type')) {
            $filters['training_type'] = self::TRAINING_TYPE_TOKENS[$trainingTypeToken];
            $summary[] = 'نوع التدريب: ' . Application::getTrainingTypeLabel($filters['training_type']);
        }

        if ($resolved = $this->resolveNamedModel(Administrative::query(), $payload['administrative_name'] ?? null, 'الإدارة')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_TRAINEES, $rawQuery, $resolved['error']);
            }

            $filters['administrative_id'] = $resolved['id'];
            $summary[] = 'الإدارة: ' . $resolved['label'];
        }

        if ($resolved = $this->resolveNamedModels(Department::query(), $payload['department_names'] ?? null, 'الدائرة')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_TRAINEES, $rawQuery, $resolved['error']);
            }

            if ($resolved['ids']) {
                $filters['department_ids'] = $resolved['ids'];
                $summary[] = 'الدوائر: ' . implode('، ', $resolved['labels']);
            }
        }

        if ($resolved = $this->resolveNamedModel(Section::query(), $payload['section_name'] ?? null, 'القسم')) {
            if ($resolved['error'] ?? false) {
                return AiTableSearchResult::failure(self::RESOURCE_TRAINEES, $rawQuery, $resolved['error']);
            }

            $filters['section_id'] = $resolved['id'];
            $summary[] = 'القسم: ' . $resolved['label'];
        }

        $keyword = $this->stringValue($payload, 'keyword');

        if ($keyword) {
            $summary[] = 'كلمة مفتاحية: ' . $keyword;
        }

        if (($filters === []) && blank($keyword)) {
            return AiTableSearchResult::failure(self::RESOURCE_TRAINEES, $rawQuery, 'الطلب غير واضح بما يكفي لتطبيق بحث ذكي آمن.');
        }

        return AiTableSearchResult::success(
            resource: self::RESOURCE_TRAINEES,
            rawQuery: $rawQuery,
            filters: $filters,
            summary: implode('، ', $summary),
            keyword: $keyword,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyApplicationsFilters(Builder $query, array $filters, ?string $keyword): Builder
    {
        $query
            ->when(isset($filters['status_ids']), fn (Builder $builder) => $builder->whereIn('status', $filters['status_ids']))
            ->when(isset($filters['training_type']), fn (Builder $builder) => $builder->where('training_type', $filters['training_type']))
            ->when(isset($filters['section_id']), fn (Builder $builder) => $builder->where('section_id', $filters['section_id']))
            ->when(isset($filters['institution_id']), fn (Builder $builder) => $builder->where('institution_id', $filters['institution_id']))
            ->when(isset($filters['college_id']), fn (Builder $builder) => $builder->where('college_id', $filters['college_id']))
            ->when(isset($filters['major_id']), fn (Builder $builder) => $builder->where('major_id', $filters['major_id']))
            ->when(isset($filters['university_number']), fn (Builder $builder) => $builder->where('university_number', 'like', '%' . $filters['university_number'] . '%'))
            ->when(isset($filters['trainee_name']), function (Builder $builder) use ($filters): void {
                $builder->whereHas('trainee', fn (Builder $traineeQuery) => $traineeQuery->where('full_name', 'like', '%' . $filters['trainee_name'] . '%'));
            })
            ->when(isset($filters['trainee_national_id']), function (Builder $builder) use ($filters): void {
                $builder->whereHas('trainee', fn (Builder $traineeQuery) => $traineeQuery->where('national_id', 'like', '%' . $filters['trainee_national_id'] . '%'));
            })
            ->when(isset($filters['administrative_id']), function (Builder $builder) use ($filters): void {
                $builder->whereHas('section', fn (Builder $sectionQuery) => $sectionQuery->where('administrative_id', $filters['administrative_id']));
            })
            ->when(isset($filters['department_ids']), function (Builder $builder) use ($filters): void {
                $builder->whereHas('section.departments', fn (Builder $departmentQuery) => $departmentQuery->whereIn('departments.id', $filters['department_ids']));
            })
            ->when(isset($filters['trainee_street']), function (Builder $builder) use ($filters): void {
                $builder->whereHas('trainee', fn (Builder $q) => $q->where('street', 'like', '%' . $filters['trainee_street'] . '%'));
            })
            ->when(isset($filters['trainee_gender']), function (Builder $builder) use ($filters): void {
                $builder->whereHas('trainee', fn (Builder $q) => $q->where('gender', $filters['trainee_gender']));
            })
            ->when(isset($filters['trainee_governorate_id']), function (Builder $builder) use ($filters): void {
                $builder->whereHas('trainee', fn (Builder $q) => $q->where('governorate_id', $filters['trainee_governorate_id']));
            })
            ->when(isset($filters['training_hours_min']), fn (Builder $builder) => $builder->where('training_hours', '>=', $filters['training_hours_min']))
            ->when(isset($filters['training_hours_max']), fn (Builder $builder) => $builder->where('training_hours', '<=', $filters['training_hours_max']));

        if (isset($filters['date_field'])) {
            if (! empty($filters['date_from'])) {
                $query->whereDate($filters['date_field'], '>=', $filters['date_from']);
            }

            if (! empty($filters['date_to'])) {
                $query->whereDate($filters['date_field'], '<=', $filters['date_to']);
            }
        }

        if (filled($keyword)) {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->whereHas('trainee', function (Builder $traineeQuery) use ($keyword): void {
                        $traineeQuery
                            ->where('full_name', 'like', '%' . $keyword . '%')
                            ->orWhere('national_id', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('section', fn (Builder $sectionQuery) => $sectionQuery->where('name', 'like', '%' . $keyword . '%'))
                    ->orWhereHas('section.administrative', fn (Builder $administrativeQuery) => $administrativeQuery->where('name', 'like', '%' . $keyword . '%'))
                    ->orWhereHas('college', fn (Builder $collegeQuery) => $collegeQuery->where('name', 'like', '%' . $keyword . '%'))
                    ->orWhereHas('institution', fn (Builder $institutionQuery) => $institutionQuery->where('name', 'like', '%' . $keyword . '%'))
                    ->orWhereHas('major', fn (Builder $majorQuery) => $majorQuery->where('name', 'like', '%' . $keyword . '%'));
            });
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyTraineesFilters(Builder $query, array $filters, ?string $keyword, ?User $user = null): Builder
    {
        $query
            ->when(isset($filters['full_name']), fn (Builder $builder) => $builder->where('full_name', 'like', '%' . $filters['full_name'] . '%'))
            ->when(isset($filters['national_id']), fn (Builder $builder) => $builder->where('national_id', 'like', '%' . $filters['national_id'] . '%'))
            ->when(isset($filters['phone_number']), fn (Builder $builder) => $builder->where('phone_number', 'like', '%' . $filters['phone_number'] . '%'))
            ->when(isset($filters['gender']), fn (Builder $builder) => $builder->where('gender', $filters['gender']))
            ->when(isset($filters['governorate_id']), fn (Builder $builder) => $builder->where('governorate_id', $filters['governorate_id']))
            ->when(isset($filters['street']), fn (Builder $builder) => $builder->where('street', 'like', '%' . $filters['street'] . '%'))
            ->when(isset($filters['age_from']), fn (Builder $builder) => $builder->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= ?', [$filters['age_from']]))
            ->when(isset($filters['age_to']), fn (Builder $builder) => $builder->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= ?', [$filters['age_to']]));

        $hasApplicationFilters = collect([
            'application_status_ids',
            'training_type',
            'administrative_id',
            'department_ids',
            'section_id',
        ])->contains(fn (string $key): bool => array_key_exists($key, $filters));

        if ($hasApplicationFilters) {
            $query->whereHas('applications', function (Builder $applicationQuery) use ($filters, $user): void {
                // Scope the inner application sub-query by the current user's authorization rules
                // to prevent cross-role application-status disclosure. Without this, a restricted
                // user (e.g. ROLE_SECTION) could probe application states outside their scope
                // through the trainee relationship.
                if ($user !== null) {
                    $applicationQuery->forUser($user);
                }

                $applicationQuery
                    ->when(isset($filters['application_status_ids']), fn (Builder $builder) => $builder->whereIn('status', $filters['application_status_ids']))
                    ->when(isset($filters['training_type']), fn (Builder $builder) => $builder->where('training_type', $filters['training_type']))
                    ->when(isset($filters['section_id']), fn (Builder $builder) => $builder->where('section_id', $filters['section_id']))
                    ->when(isset($filters['administrative_id']), function (Builder $builder) use ($filters): void {
                        $builder->whereHas('section', fn (Builder $sectionQuery) => $sectionQuery->where('administrative_id', $filters['administrative_id']));
                    })
                    ->when(isset($filters['department_ids']), function (Builder $builder) use ($filters): void {
                        $builder->whereHas('section.departments', fn (Builder $departmentQuery) => $departmentQuery->whereIn('departments.id', $filters['department_ids']));
                    });
            });
        }

        if (filled($keyword)) {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('full_name', 'like', '%' . $keyword . '%')
                    ->orWhere('national_id', 'like', '%' . $keyword . '%')
                    ->orWhere('phone_number', 'like', '%' . $keyword . '%')
                    ->orWhereHas('governorate', fn (Builder $governorateQuery) => $governorateQuery->where('name', 'like', '%' . $keyword . '%'))
                    ->orWhereHas('applications.section', fn (Builder $sectionQuery) => $sectionQuery->where('name', 'like', '%' . $keyword . '%'))
                    ->orWhereHas('applications.section.administrative', fn (Builder $administrativeQuery) => $administrativeQuery->where('name', 'like', '%' . $keyword . '%'));
            });
        }

        return $query;
    }

    /**
     * @param  array<int, int>|null  $statusIds
     */
    private function validateApplicationsTab(?array $statusIds, ?string $activeTab): ?string
    {
        if (! $activeTab || $statusIds === null || $statusIds === []) {
            return null;
        }

        // The 'all' tab excludes rejected applications. If every requested status is rejected,
        // the combined query produces zero results silently.
        if ($activeTab === 'all' && $statusIds === [Application::STATUS_REJECTED]) {
            return 'الطلبات المرفوضة لا تظهر في تبويب "الكل". انتقل إلى تبويب المرفوضات أولاً ثم أعد البحث.';
        }

        if (! array_key_exists($activeTab, self::APPLICATION_TAB_TO_STATUS)) {
            return null;
        }

        $tabStatus = self::APPLICATION_TAB_TO_STATUS[$activeTab];

        // Allow if any of the requested statuses matches the current tab
        if (in_array($tabStatus, $statusIds, true)) {
            return null;
        }

        return 'طلب البحث يشير إلى حالة مختلفة عن التبويب الحالي. غيّر التبويب أولاً ثم أعد المحاولة.';
    }

    private function normalizeFailureReason(mixed $reason): string
    {
        $reason = is_string($reason) ? trim($reason) : '';

        return $reason !== '' ? $reason : 'لا يمكن تفسير هذا الطلب بشكل آمن.';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function stringValue(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        if (! is_string($value)) {
            return null;
        }

        $value = Str::squish($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param  Builder<Model>  $query
     * @return array{id?: int, label?: string, error?: string}
     */
    private function resolveNamedModel(Builder $query, mixed $value, string $label): array
    {
        if (! is_string($value) || ($value = Str::squish($value)) === '') {
            return [];
        }

        return $this->resolveSingleName($query, $value, $label);
    }

    /**
     * @param  Builder<Model>  $query
     * @return array{ids: array<int, int>, labels: array<int, string>, error?: string}
     */
    private function resolveNamedModels(Builder $query, mixed $values, string $label): array
    {
        if (! is_array($values)) {
            return ['ids' => [], 'labels' => []];
        }

        $ids = [];
        $labels = [];

        foreach ($values as $value) {
            if (! is_string($value) || ($value = Str::squish($value)) === '') {
                continue;
            }

            $resolved = $this->resolveSingleName(clone $query, $value, $label);

            if ($resolved['error'] ?? false) {
                return ['ids' => [], 'labels' => [], 'error' => $resolved['error']];
            }

            $ids[] = $resolved['id'];
            $labels[] = $resolved['label'];
        }

        return [
            'ids' => array_values(array_unique($ids)),
            'labels' => array_values(array_unique($labels)),
        ];
    }

    /**
     * @param  Builder<Model>  $query
     * @return array{id?: int, label?: string, error?: string}
     */
    private function resolveSingleName(Builder $query, string $value, string $label): array
    {
        // Strip common Arabic structural prefixes the model may leave in the name.
        $stripped = preg_replace('/^(دائرة|قسم|إدارة|مديرية|وحدة|شعبة|مركز)\s+/u', '', $value);
        if ($stripped !== $value && filled($stripped)) {
            $result = $this->resolveSingleName(clone $query, $stripped, $label);
            if (! ($result['error'] ?? false)) {
                return $result;
            }
        }

        $matches = (clone $query)
            ->where('name', $value)
            ->limit(2)
            ->get(['id', 'name']);

        if ($matches->count() === 1) {
            return [
                'id' => $matches->first()->getKey(),
                'label' => $matches->first()->getAttribute('name'),
            ];
        }

        // Escape LIKE special characters so a name containing '%' or '_' does not
        // produce a broader-than-intended pattern match.
        $likeValue = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);

        $matches = (clone $query)
            ->where('name', 'like', '%' . $likeValue . '%')
            ->limit(3)
            ->get(['id', 'name']);

        if ($matches->count() === 1) {
            return [
                'id' => $matches->first()->getKey(),
                'label' => $matches->first()->getAttribute('name'),
            ];
        }

        if ($matches->isEmpty()) {
            return ['error' => "لم يتم العثور على {$label} المطابق لعبارة: {$value}."];
        }

        return ['error' => "يوجد أكثر من {$label} يطابق العبارة: {$value}. يرجى التحديد بشكل أوضح."];
    }
}
