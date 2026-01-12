<?php

namespace App\Livewire\Welcome;

use App\Models\TrainingSetting;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Title;

/**
 * WelcomeForm Livewire Component
 * 
 * Landing page for trainee application form.
 * Provides navigation and introduction to the training system.
 * 
 * Replaces vanilla JavaScript welcome/app.js with Livewire component.
 */
#[Title('صفحة الترحيب - نظام التدريب')]
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

    public function mount(): void
    {
        $settings = TrainingSetting::getInstance();

        $this->isFormEnabled = (bool) $settings->is_public_form_enabled;
        $this->formUuid = (string) Str::uuid();
    }

    /**
     * Component doesn't need much state management,
     * just rendering the welcome view.
     */
    public function render()
    {
        return view('livewire.welcome.welcome-form')->layout('components.layouts.app');
    }
}
