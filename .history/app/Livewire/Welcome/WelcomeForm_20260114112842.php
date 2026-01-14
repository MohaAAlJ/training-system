<?php

namespace App\Livewire\Welcome;

use App\Settings\TrainingSettings;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

/**
 * WelcomeForm Livewire Component
 * 
 * Landing page for trainee application form.
 * Provides navigation and introduction to the training system.
 * 
 * Replaces vanilla JavaScript welcome/app.js with Livewire component.
 */
#[Title('صفحة الترحيب - نظام التدريب')]
#[Layout('components.layouts.app')]
class WelcomeForm extends Component
{
    /**
     * Toggle for whether the public form is open.
     */
    public bool $isFormEnabled = false;

    /**
     * Unique identifier for this session to mirror legacy behavior.
     */
    public string $formUuid = '';

    /**
     * Fields for checking re-application
     */
    public ?string $nationalId = null;
    public ?int $trainingType = null;
    public $trainingTypes = [];

    public function mount(): void
    {
        $settings = app(TrainingSettings::class);

        $this->isFormEnabled = (bool) $settings->is_public_form_enabled;
        $this->formUuid = (string) Str::uuid();

        $this->loadTrainingTypes($settings);
    }

    private function loadTrainingTypes(TrainingSettings $settings): void
    {
        $this->trainingTypes = \App\Models\Application::getEnabledTrainingTypes();

        // Auto-select if only one option
        if (count($this->trainingTypes) === 1) {
            $this->trainingType = $this->trainingTypes[0]['id'];
        }
    }

    public function checkApplication()
    {
        $this->validate([
            'nationalId' => 'required|digits:9',
            'trainingType' => 'required',
        ], [
            'nationalId.required' => 'يرجى إدخال رقم الهوية',
            'nationalId.digits' => 'رقم الهوية يجب أن يتكون من 9 أرقام',
            'trainingType.required' => 'يرجى اختيار نوع التدريب',
        ]);

        $check = \App\Models\Application::checkEligibility($this->nationalId, (int) $this->trainingType);

        if ($check['status'] === 'blocked') {
            $this->addError('nationalId', $check['message']);
            return;
        }

        return redirect()->route('training.form', [
            'national_id' => $this->nationalId,
            'training_type' => $this->trainingType,
        ]);
    }

    /**
     * Component doesn't need much state management,
     * just rendering the welcome view.
     */
    public function render()
    {
        return view('livewire.welcome.welcome-form');
    }
}
