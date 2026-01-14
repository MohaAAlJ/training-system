<?php

namespace Tests\Feature;

use App\Livewire\Trainee\TraineeForm;
use App\Models\Application;
use App\Models\Trainee;
use App\Settings\TrainingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class TraineeFormLogicTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Clear cache
        Cache::flush();
        // Disable FK compliance to allowing creating Trainee with partial data/valid IDs without creating related records
        Schema::disableForeignKeyConstraints();
    }

    /** @test */
    public function it_prefills_form_when_valid_trainee_found()
    {
        // Mock Settings
        $settings = \Mockery::mock(TrainingSettings::class);
        $settings->can_university_reapply = true;
        $settings->can_practice_reapply = true;
        $this->app->instance(TrainingSettings::class, $settings);

        // Manually create Trainee with required fields
        $trainee = new Trainee();
        $trainee->forceFill([
            'national_id' => '123456789',
            'dob' => '2000-01-01',
            'first_name' => 'John',
            'governorate_id' => 1,
            'institution_id' => 1,
            'college_id' => 1,
            'major_id' => 1,
            'street' => 'Test St',
            'phone_number' => '123123',
            'full_name' => 'John Doe',
            'training_hours' => 100
        ]);
        $trainee->save();

        Livewire::test(TraineeForm::class)
            ->set('nationalId', '123456789')
            ->set('trainingType', Application::TRAINING_TYPE_UNIVERSITY)
            ->set('dob', '2000-01-01')
            ->call('updatedDob')
            ->assertSet('first_name', 'John')
            ->assertSet('showPersonalDetails', true);
    }

    /** @test */
    public function it_caches_trainee_data_and_uses_it_on_next_request()
    {
        // Mock Settings
        $settings = \Mockery::mock(TrainingSettings::class);
        $settings->can_university_reapply = true;
        $settings->can_practice_reapply = true;
        $this->app->instance(TrainingSettings::class, $settings);

        $trainee = new Trainee();
        $trainee->forceFill([
            'national_id' => '987654321',
            'dob' => '1999-12-31',
            'first_name' => 'Jane',
            'full_name' => 'Jane Doe',
            'phone_number' => '1234567890',
            'street' => 'Test Street',
            'governorate_id' => 1,
            'institution_id' => 1,
            'college_id' => 1,
            'major_id' => 1,
            'training_hours' => 100,
        ]);
        $trainee->save();

        // 1. First Request
        Livewire::test(TraineeForm::class)
            ->set('nationalId', '987654321')
            ->set('trainingType', Application::TRAINING_TYPE_UNIVERSITY)
            ->set('dob', '1999-12-31')
            ->call('updatedDob');

        // 2. Check Cache
        $cacheKey = "app_status:987654321:" . Application::TRAINING_TYPE_UNIVERSITY . ":1999-12-31:1";
        $this->assertTrue(Cache::has($cacheKey), 'Cache key should exist');

        $cachedResult = Cache::get($cacheKey);
        $this->assertArrayHasKey('trainee_data', $cachedResult);
        $this->assertEquals('Jane', $cachedResult['trainee_data']['first_name']);

        // 3. Second Request (Simulate Data Loss in DB but Cache Hit)
        $trainee->delete();

        Livewire::test(TraineeForm::class)
            ->set('nationalId', '987654321')
            ->set('trainingType', Application::TRAINING_TYPE_UNIVERSITY)
            ->set('dob', '1999-12-31')
            ->call('updatedDob')
            ->assertSet('first_name', 'Jane')
            ->assertSet('showPersonalDetails', true);
    }

    /** @test */
    public function it_blocks_reapplication_when_settings_disabled()
    {
        // Mock Settings - return FALSE
        $settings = \Mockery::mock(TrainingSettings::class);
        $settings->can_university_reapply = false;
        $settings->can_practice_reapply = false;
        $this->app->instance(TrainingSettings::class, $settings);

        $trainee = new Trainee();
        $trainee->forceFill([
            'national_id' => '111222333',
            'dob' => '2002-05-05',
            'first_name' => 'BlockMe',
            'full_name' => 'Block Me',
            'phone_number' => '1234567890',
            'street' => 'Block St',
            'governorate_id' => 1,
            'institution_id' => 1,
            'college_id' => 1,
            'major_id' => 1,
            'training_hours' => 100,
        ]);
        $trainee->save();

        // Manual Application creation
        $app = new Application();
        $app->forceFill([
            'uuid' => 'test-uuid-1',
            'trainee_id' => $trainee->id,
            'training_type' => Application::TRAINING_TYPE_PRACTICE,
            'status' => Application::STATUS_ENDED_TRAINING, // Finished
            'start_date' => '2023-01-01',
            'end_date' => '2023-02-01',
        ]);
        $app->save();

        Livewire::test(TraineeForm::class)
            ->set('nationalId', '111222333')
            ->set('trainingType', Application::TRAINING_TYPE_PRACTICE)
            ->set('dob', '2002-05-05')
            ->call('updatedDob')
            ->assertSet('showPersonalDetails', false)
            ->assertDispatched('toast', function ($eventName, $params) {
                return $params['type'] === 'error'; // Should error
            });
    }

    /** @test */
    public function it_allows_reapplication_when_settings_enabled()
    {
        // Mock Settings - return TRUE
        $settings = \Mockery::mock(TrainingSettings::class);
        $settings->can_university_reapply = true;
        $settings->can_practice_reapply = true;
        $this->app->instance(TrainingSettings::class, $settings);

        $trainee = new Trainee();
        $trainee->forceFill([
            'national_id' => '111222333',
            'dob' => '2002-05-05',
            'full_name' => 'Allow Me',
            'phone_number' => '1234567890',
            'street' => 'Allow St',
            'governorate_id' => 1,
            'institution_id' => 1,
            'college_id' => 1,
            'major_id' => 1,
            'training_hours' => 100,
        ]);
        $trainee->save();

        $app = new Application();
        $app->forceFill([
            'uuid' => 'test-uuid-2',
            'trainee_id' => $trainee->id,
            'training_type' => Application::TRAINING_TYPE_PRACTICE,
            'status' => Application::STATUS_ENDED_TRAINING,
            'start_date' => '2023-01-01',
            'end_date' => '2023-02-01',
        ]);
        $app->save();

        Livewire::test(TraineeForm::class)
            ->set('nationalId', '111222333')
            ->set('trainingType', Application::TRAINING_TYPE_PRACTICE)
            ->set('dob', '2002-05-05')
            ->call('updatedDob')
            ->assertSet('showPersonalDetails', true);
    }
}
