<?php

namespace App\Livewire\Welcome;

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
     * Component doesn't need much state management,
     * just rendering the welcome view.
     */
    public function render()
    {
        return view('livewire.welcome.welcome-form')->layout('components.layouts.app');
    }
}
