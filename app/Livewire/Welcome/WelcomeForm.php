<?php

namespace App\Livewire\Welcome;

use App\Settings\TrainingSettings;
use Illuminate\Support\Facades\Log;
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
     * One-click start application
     */
    public function startApplication()
    {
        $settings = app(TrainingSettings::class);
        $isFormEnabled = (bool) $settings->is_public_form_enabled;
        $applicationsCount = \App\Models\Application::count();

        return redirect()->route('training.form');
    }

    /**
     * Component doesn't need much state management,
     * just rendering the welcome view.
     */
    public function render()
    {
        $settings = app(TrainingSettings::class);
        $isFormEnabled = (bool) $settings->is_public_form_enabled;
        $applicationsCount = \App\Models\Application::count();

        return view('livewire.welcome.welcome-form', [
            'isFormEnabled' => $isFormEnabled,
            'applicationsCount' => $applicationsCount,
        ]);
    }
}
