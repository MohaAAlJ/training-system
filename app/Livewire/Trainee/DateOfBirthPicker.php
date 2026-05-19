<?php

namespace App\Livewire\Trainee;

use Livewire\Component;
use Carbon\Carbon;
use App\Livewire\Trainee\Config\TraineeFormConfig;

class DateOfBirthPicker extends Component
{
    public string $dob = '';

    public function mount($dob = null)
    {
        if ($dob) {
            $this->dob = $dob;
        } else {
            $this->dob = now()->subYears(TraineeFormConfig::MIN_AGE)->format('Y-m-d');
        }
    }

    public function updatedDob($value)
    {
        if ($value) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $value);
                $this->dob = $date->format('Y-m-d');
                // Dispatch to parent component
                $this->dispatch('update-dob-alpine', dob: $this->dob);
            } catch (\Exception $e) {
                // Invalid date
            }
        }
    }

    public function render()
    {
        return view('livewire.trainee.date-of-birth-picker');
    }
}
