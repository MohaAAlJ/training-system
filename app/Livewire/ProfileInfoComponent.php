<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;

class ProfileInfoComponent extends Component
{
    use HasSort;

    public static int $sort = 10;

    // Phone change flow states
     protected static ?string $slug = 'profile';
    public bool $confirming = false;
    public bool $editing = false;
    public string $newPhone = '';
    public ?string $successMessage = null;

    public function startChange(): void
    {
        $this->confirming = true;
        $this->editing = false;
        $this->newPhone = '';
        $this->successMessage = null;
    }

    public function confirmChange(): void
    {
        $this->confirming = false;
        $this->editing = true;
        $this->newPhone = Auth::user()->phone_number ?? '';
    }

    public function cancelChange(): void
    {
        $this->confirming = false;
        $this->editing = false;
        $this->newPhone = '';
    }

    public function savePhone(): void
    {
        $this->validate([
            'newPhone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s]{7,20}$/',
            ],
        ], [
            'newPhone.required'  => 'رقم الهاتف مطلوب.',
            'newPhone.regex'     => 'صيغة رقم الهاتف غير صحيحة.',
        ]);

        $user = Auth::user();
        $user->phone_number = $this->newPhone;
        $user->save();

        $this->editing = false;
        $this->confirming = false;
        $this->newPhone = '';
        $this->successMessage = 'تم تحديث رقم الهاتف بنجاح.';
    }

    public function render()
    {
        return view('livewire.profile-info-component', [
            'user' => Auth::user(),
        ]);
    }
}
