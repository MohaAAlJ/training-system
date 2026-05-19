<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Trainees\Pages\ListTrainees;
use App\Models\Application;
use App\Models\Section;
use App\Models\Trainee;
use App\Models\User;
use App\Settings\TrainingSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Laravel\Ai\StructuredAnonymousAgent;
use Tests\TestCase;

class AiTableSearchPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('home'));
    }

    public function test_applications_page_applies_ai_search_without_changing_the_tab(): void
    {
        $user = User::factory()->admin()->create();
        $settings = app(TrainingSettings::class);
        $settings->ai_search_enabled = true;
        $settings->ai_search_allowed_roles = [User::ROLE_ADMIN];
        $settings->save();

        $waitingTrainee = Trainee::factory()->create(['full_name' => 'أحمد الانتظار']);
        $otherTrainee = Trainee::factory()->create(['full_name' => 'سارة التدريب']);
        $section = Section::factory()->create(['active' => true]);

        $waitingApplication = Application::factory()->create([
            'trainee_id' => $waitingTrainee->id,
            'section_id' => $section->id,
            'status' => Application::STATUS_WAITING_LIST,
        ]);

        $trainingApplication = Application::factory()->create([
            'trainee_id' => $otherTrainee->id,
            'section_id' => $section->id,
            'status' => Application::STATUS_STARTED_TRAINING,
        ]);

        StructuredAnonymousAgent::fake([
            [
                'is_supported' => true,
                'reason' => null,
                'keyword' => null,
                'status' => 'waiting_list',
                'training_type' => null,
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

        $this->actingAs($user);

        Livewire::test(ListApplications::class)
            ->set('activeTab', 'all')
            ->set('aiSearchPrompt', 'طلبات الانتظار لأحمد')
            ->call('applyAiSearch')
            ->assertSet('activeTab', 'all')
            ->assertSet('aiSearchSummary', 'الحالة: قائمة الانتظار، الاسم: أحمد')
            ->assertCanSeeTableRecords([$waitingApplication])
            ->assertCanNotSeeTableRecords([$trainingApplication]);
    }

    public function test_forged_ai_search_requests_are_blocked_when_feature_is_disabled(): void
    {
        $user = User::factory()->admin()->create();
        $settings = app(TrainingSettings::class);
        $settings->ai_search_enabled = false;
        $settings->ai_search_allowed_roles = [User::ROLE_ADMIN];
        $settings->save();

        StructuredAnonymousAgent::fake([
            [
                'is_supported' => true,
                'reason' => null,
                'keyword' => null,
                'full_name' => 'أحمد',
                'national_id' => null,
                'phone_number' => null,
                'gender' => null,
                'governorate_name' => null,
                'application_status' => null,
                'training_type' => null,
                'administrative_name' => null,
                'department_names' => [],
                'section_name' => null,
            ],
        ]);

        $this->actingAs($user);

        Livewire::test(ListTrainees::class)
            ->set('aiSearchPrompt', 'ابحث عن أحمد')
            ->call('applyAiSearch')
            ->assertSet('aiSearchSummary', null)
            ->assertSet('aiSearchFilters', null);
    }

    /**
     * Regression for: the 'all' tab + rejected status silently produced zero rows.
     * The 'all' tab's own modifyQueryUsing adds status != rejected. When the AI
     * resolves status = rejected on that tab the interpreter must reject the request
     * before it reaches the table, not let the contradictory WHERE clauses silently
     * eliminate all rows.
     */
    public function test_searching_rejected_on_all_tab_returns_an_error_not_empty_results(): void
    {
        $user = User::factory()->admin()->create();
        $settings = app(TrainingSettings::class);
        $settings->ai_search_enabled = true;
        $settings->ai_search_allowed_roles = [User::ROLE_ADMIN];
        $settings->save();

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

        $this->actingAs($user);

        Livewire::test(ListApplications::class)
            ->set('activeTab', 'all')
            ->set('aiSearchPrompt', 'الطلبات المرفوضة')
            ->call('applyAiSearch')
            // Filters must NOT be applied — a failure notification is sent instead
            ->assertSet('aiSearchFilters', null)
            ->assertSet('aiSearchSummary', null);
    }
}
