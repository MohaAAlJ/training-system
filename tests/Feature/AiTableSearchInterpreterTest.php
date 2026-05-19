<?php

namespace Tests\Feature;

use App\Models\Administrative;
use App\Models\Application;
use App\Models\Department;
use App\Models\Governorate;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Section;
use App\Models\User;
use App\Services\AiSearch\AiTableSearchInterpreter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\StructuredAnonymousAgent;
use Tests\TestCase;

class AiTableSearchInterpreterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_interprets_application_filters_within_the_current_tab(): void
    {
        $user = User::factory()->admin()->create();

        StructuredAnonymousAgent::fake([
            [
                'is_supported' => true,
                'reason' => null,
                'keyword' => null,
                'status' => 'waiting_list',
                'training_type' => 'practice',
                'trainee_name' => 'أحمد',
                'trainee_national_id' => null,
                'administrative_name' => null,
                'department_names' => [],
                'section_name' => null,
                'institution_name' => null,
                'college_name' => null,
                'major_name' => null,
                'date_field' => null,
                'date_from' => null,
                'date_to' => null,
            ],
        ]);

        $result = app(AiTableSearchInterpreter::class)->interpret(
            resource: 'applications',
            rawQuery: 'المتدربين بقائمة الانتظار العملي',
            user: $user,
            context: ['active_tab' => 'waiting_list'],
        );

        $this->assertTrue($result->success);
        $this->assertSame(Application::STATUS_WAITING_LIST, $result->filters['status']);
        $this->assertSame(Application::PRACTICE, $result->filters['training_type']);
        $this->assertSame('أحمد', $result->filters['trainee_name']);
    }

    public function test_it_fails_when_the_applications_query_conflicts_with_the_active_tab(): void
    {
        $user = User::factory()->admin()->create();

        StructuredAnonymousAgent::fake([
            [
                'is_supported' => true,
                'reason' => null,
                'keyword' => null,
                'status' => 'waiting_list',
                'training_type' => null,
                'trainee_name' => null,
                'trainee_national_id' => null,
                'administrative_name' => null,
                'department_names' => [],
                'section_name' => null,
                'institution_name' => null,
                'college_name' => null,
                'major_name' => null,
                'date_field' => null,
                'date_from' => null,
                'date_to' => null,
            ],
        ]);

        $result = app(AiTableSearchInterpreter::class)->interpret(
            resource: 'applications',
            rawQuery: 'طلبات قائمة الانتظار',
            user: $user,
            context: ['active_tab' => 'training'],
        );

        $this->assertFalse($result->success);
        $this->assertStringContainsString('التبويب الحالي', $result->error ?? '');
    }

    public function test_it_resolves_named_application_entities_and_dates(): void
    {
        $user = User::factory()->admin()->create();
        $institution = Institution::factory()->create(['name' => 'جامعة القدس']);
        $major = Major::factory()->create(['name' => 'تمريض']);

        StructuredAnonymousAgent::fake([
            [
                'is_supported' => true,
                'reason' => null,
                'keyword' => null,
                'status' => null,
                'training_type' => null,
                'trainee_name' => null,
                'trainee_national_id' => null,
                'administrative_name' => null,
                'department_names' => [],
                'section_name' => null,
                'institution_name' => 'جامعة القدس',
                'college_name' => null,
                'major_name' => 'تمريض',
                'date_field' => 'start_date',
                'date_from' => '2026-04-01',
                'date_to' => '2026-04-30',
                'keyword' => null,
            ],
        ]);

        $result = app(AiTableSearchInterpreter::class)->interpret(
            resource: 'applications',
            rawQuery: 'طلبات جامعة القدس بتخصص تمريض هذا الشهر',
            user: $user,
            context: ['active_tab' => 'all'],
        );

        $this->assertTrue($result->success);
        $this->assertSame($institution->id, $result->filters['institution_id']);
        $this->assertSame($major->id, $result->filters['major_id']);
        $this->assertSame('start_date', $result->filters['date_field']);
        $this->assertSame('2026-04-01', $result->filters['date_from']);
        $this->assertSame('2026-04-30', $result->filters['date_to']);
    }

    public function test_it_interprets_trainee_queries_with_related_application_filters(): void
    {
        $user = User::factory()->admin()->create();
        $administrative = Administrative::factory()->create(['name' => 'الإدارة الطبية']);
        $department = Department::factory()->create(['name' => 'المختبر', 'active' => true, 'visible' => true]);
        $section = Section::factory()->create([
            'name' => 'قسم التحاليل',
            'administrative_id' => $administrative->id,
            'active' => true,
        ]);
        $section->departments()->attach($department);
        $governorate = Governorate::factory()->create(['name' => 'رام الله']);

        StructuredAnonymousAgent::fake([
            [
                'is_supported' => true,
                'reason' => null,
                'keyword' => null,
                'full_name' => null,
                'national_id' => null,
                'phone_number' => null,
                'gender' => 'female',
                'governorate_name' => 'رام الله',
                'application_status' => 'waiting_list',
                'training_type' => null,
                'administrative_name' => 'الإدارة الطبية',
                'department_names' => ['المختبر'],
                'section_name' => 'قسم التحاليل',
            ],
        ]);

        $result = app(AiTableSearchInterpreter::class)->interpret(
            resource: 'trainees',
            rawQuery: 'المتدربات بقائمة الانتظار في قسم التحاليل برام الله',
            user: $user,
        );

        $this->assertTrue($result->success);
        $this->assertSame($governorate->id, $result->filters['governorate_id']);
        $this->assertSame($administrative->id, $result->filters['administrative_id']);
        $this->assertSame($section->id, $result->filters['section_id']);
        $this->assertSame([$department->id], $result->filters['department_ids']);
        $this->assertSame(Application::STATUS_WAITING_LIST, $result->filters['application_status']);
    }

    public function test_it_fails_closed_for_unsupported_requests(): void
    {
        $user = User::factory()->admin()->create();

        StructuredAnonymousAgent::fake([
            [
                'is_supported' => false,
                'reason' => 'هذا الطلب يحتاج إلى حسابات غير مدعومة.',
                'keyword' => null,
                'status' => null,
                'training_type' => null,
                'trainee_name' => null,
                'trainee_national_id' => null,
                'administrative_name' => null,
                'department_names' => [],
                'section_name' => null,
                'institution_name' => null,
                'college_name' => null,
                'major_name' => null,
                'date_field' => null,
                'date_from' => null,
                'date_to' => null,
            ],
        ]);

        $result = app(AiTableSearchInterpreter::class)->interpret(
            resource: 'applications',
            rawQuery: 'اعرض أعلى نسبة إشغال متوقعة الأسبوع القادم',
            user: $user,
            context: ['active_tab' => 'all'],
        );

        $this->assertFalse($result->success);
        $this->assertSame('هذا الطلب يحتاج إلى حسابات غير مدعومة.', $result->error);
    }

    /**
     * Regression for: validateApplicationsTab was blind to the 'all' tab.
     * The 'all' tab's modifyQueryUsing adds status != rejected. If the AI resolves
     * status = rejected the combined query silently returns zero rows. The interpreter
     * must catch this and return a failure instead.
     */
    public function test_it_fails_when_rejected_status_is_requested_on_the_all_tab(): void
    {
        $user = User::factory()->admin()->create();

        StructuredAnonymousAgent::fake([
            [
                'is_supported' => true,
                'reason' => null,
                'keyword' => null,
                'status' => 'rejected',
                'training_type' => null,
                'trainee_name' => null,
                'trainee_national_id' => null,
                'administrative_name' => null,
                'department_names' => [],
                'section_name' => null,
                'institution_name' => null,
                'college_name' => null,
                'major_name' => null,
                'date_field' => null,
                'date_from' => null,
                'date_to' => null,
            ],
        ]);

        $result = app(AiTableSearchInterpreter::class)->interpret(
            resource: 'applications',
            rawQuery: 'الطلبات المرفوضة',
            user: $user,
            context: ['active_tab' => 'all'],
        );

        $this->assertFalse($result->success);
        $this->assertNotNull($result->error);
        $this->assertStringContainsString('المرفوض', $result->error);
    }

    /**
     * Regression for: applyTraineesFilters inner whereHas('applications') was unscoped.
     * A restricted user (ROLE_SECTION) could probe application statuses outside their
     * authorization scope through the trainee relation. After the fix the inner
     * application sub-query is scoped by forUser(), so an out-of-scope status filter
     * produces no results rather than leaking cross-role data.
     */
    public function test_section_head_cannot_probe_unauthorized_application_statuses_via_trainee_search(): void
    {
        $sectionUser = User::factory()->section()->create();
        // Section belongs to the user via the user_id FK on sections.
        $section = \App\Models\Section::factory()->create([
            'user_id' => $sectionUser->id,
            'active' => true,
        ]);

        $trainee = \App\Models\Trainee::factory()->create(['full_name' => 'محمد اختبار']);

        // Visible application: in the section head's section, status = waiting_list
        \App\Models\Application::factory()->create([
            'trainee_id' => $trainee->id,
            'section_id' => $section->id,
            'status' => Application::STATUS_WAITING_LIST,
            'training_type' => Application::UNIVERSITY,
        ]);

        // Out-of-scope application: in a different section, status = new
        $otherSection = \App\Models\Section::factory()->create(['active' => true]);
        \App\Models\Application::factory()->create([
            'trainee_id' => $trainee->id,
            'section_id' => $otherSection->id,
            'status' => Application::STATUS_NEW,
            'training_type' => Application::UNIVERSITY,
        ]);

        $interpreter = app(AiTableSearchInterpreter::class);

        // Apply a trainee filter for application_status = new (out-of-scope for section head)
        $query = \App\Models\Trainee::query();
        $interpreter->applyToQuery(
            resource: 'trainees',
            query: $query,
            filters: ['application_status' => Application::STATUS_NEW],
            keyword: null,
            user: $sectionUser,
        );

        // The section head's forUser scope restricts inner applications to their section
        // with statuses [waiting_list, started_training, ended_training].
        // STATUS_NEW is not in that set, so no trainee should appear.
        $this->assertSame(0, $query->count());
    }
}
