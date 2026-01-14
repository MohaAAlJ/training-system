<?php

namespace Tests\Feature;

use App\Livewire\Trainee\TraineeForm;
use App\Models\Application;
use App\Models\Trainee;
use App\Models\TrainingSetting;
use App\Settings\TrainingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class TraineeFormLogicTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_prefills_form_when_valid_trainee_found()
    {
        // Mock Settings
        $settings = \Mockery::mock(TrainingSettings::class);
        $settings->can_university_reapply = true;
        $settings->can_practice_reapply = true;
        $this->app->instance(TrainingSettings::class, $settings);

        $trainee = Trainee::factory()->create([
            'national_id' => '123456789',
            'dob' => '2000-01-01',
            'first_name' => 'John',
        ]);

        Livewire::test(TraineeForm::class)
            ->set('nationalId', '123456789')
            ->set('trainingType', Application::TRAINING_TYPE_UNIVERSITY)
            ->set('dob', '2000-01-01')
            ->call('updatedDob') // Simulate blur sequence triggering checkApplicationStatus
            ->assertSet('first_name', 'John')
            ->assertSet('showPersonalDetails', true);
    }

    /** @test */
    public function it_caches_trainee_data_and_uses_it_on_next_request()
    {
        $trainee = Trainee::factory()->create([
            'national_id' => '987654321',
            'dob' => '1999-12-31',
            'first_name' => 'Jane',
        ]);

        $settings = app(TrainingSettings::class);
        $settings->can_university_reapply = true;
        $settings->save();

        // 1. First Request: Should query DB and Cache result
        Livewire::test(TraineeForm::class)
            ->set('nationalId', '987654321')
            ->set('trainingType', Application::TRAINING_TYPE_UNIVERSITY)
            ->set('dob', '1999-12-31')
            ->call('updatedDob'); // Triggers check

        // 2. Check Cache existence
        $cacheKey = "app_status:987654321:" . Application::TRAINING_TYPE_UNIVERSITY . ":1999-12-31:1";
        $this->assertTrue(Cache::has($cacheKey), 'Cache key should exist');

        $cachedResult = Cache::get($cacheKey);
        $this->assertArrayHasKey('trainee_data', $cachedResult);
        $this->assertEquals('Jane', $cachedResult['trainee_data']['first_name']);

        // 3. Second Request: Simulate by deleting Trainee from DB but keeping Cache
        // If it relies on cache, it should still find Jane
        // Note: Logic inside checkApplicationStatus checks DB result first for existence of trainee record usually?
        // Wait, the code checks `Trainee::where(...)` BEFORE checking cache? NO.
        // The code checks CACHE FIRST.

        // So if I delete trainee from DB, but cache exists, it SHOULD work and prefill.
        $trainee->delete();

        Livewire::test(TraineeForm::class)
            ->set('nationalId', '987654321')
            ->set('trainingType', Application::TRAINING_TYPE_UNIVERSITY)
            ->set('dob', '1999-12-31')
            ->call('updatedDob')
            ->assertSet('first_name', 'Jane') // Proven it came from cache
            ->assertSet('showPersonalDetails', true);
    }

    /** @test */
    public function it_blocks_reapplication_when_settings_disabled()
    {
        // 1. Setup Data
        $trainee = Trainee::factory()->create([
            'national_id' => '111222333',
            'dob' => '2002-05-05',
        ]);

        Application::factory()->create([
            'trainee_id' => $trainee->id,
            'training_type' => Application::TRAINING_TYPE_PRACTICE,
            'status' => Application::STATUS_ENDED_TRAINING,
        ]);

        // 2. Disable Re-application
        $settings = app(TrainingSettings::class);
        $settings->can_practice_reapply = false;
        $settings->save();

        // 3. Test Blocked
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
        // 1. Setup Data
        $trainee = Trainee::factory()->create([
            'national_id' => '111222333',
            'dob' => '2002-05-05',
        ]);

        Application::factory()->create([
            'trainee_id' => $trainee->id,
            'training_type' => Application::TRAINING_TYPE_PRACTICE,
            'status' => Application::STATUS_ENDED_TRAINING,
        ]);

        // 2. Enable Re-application
        $settings = app(TrainingSettings::class);
        $settings->can_practice_reapply = true;
        $settings->save();

        // 3. Test Allowed
        Livewire::test(TraineeForm::class)
            ->set('nationalId', '111222333')
            ->set('trainingType', Application::TRAINING_TYPE_PRACTICE)
            ->set('dob', '2002-05-05')
            ->call('updatedDob')
            ->assertSet('showPersonalDetails', true);
    }
}
