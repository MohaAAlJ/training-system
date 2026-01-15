<link rel="stylesheet" href="{{ asset('css/fieldset-training-type.css') }}">

<!-- FIELDSET 1: Training Type & Basic Info -->
<fieldset class="fieldset">
    <legend>
        <span class="legend-icon">🔍</span>التحقق من الطلب
    </legend>
    <div class="grid two">
        <!-- Training Type Selection -->
        <label class="field">
            <span>نوع التدريب *</span>
            <select wire:model.live="trainingType" class="form__input" required :disabled="$wire.isValidating">
                <option value="">-- اختر نوع التدريب --</option>
                @foreach ($trainingTypes as $type)
                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <small class="note">اختر نوع التدريب المناسب</small>
        </label>

        <!-- National ID Input -->
        <label class="field">
            <span>رقم الهوية *</span>
            <input 
                type="text" 
                wire:model.blur="nationalId"
                wire:blur="validatePalestinianIdOnBlur"
                pattern="[0-9]*"
                placeholder="رقم الهوية"
                maxlength="9"
                class="form__input @error('nationalId') form__input--error @enderror"
                required
                @readonly($nationalIdReadonly)
                :disabled="$wire.isValidating || $wire.nationalIdReadonly"
            >
            <small class="note">رقم الهوية (9 أرقام)</small>
        </label>

        <!-- Date of Birth -->
        <label class="field">
            <span>تاريخ الميلاد *</span>
            <input 
                type="date" 
                wire:model.blur="dob"
                class="form__input @error('dob') form__input--error @enderror"
                required
                required
                @readonly($dobReadonly)
                @disabled($isValidating || $dobReadonly)
                min="{{ now()->subYears(App\Livewire\Trainee\Config\TraineeFormConfig::MAX_AGE)->format('Y-m-d') }}"
                max="{{ now()->subYears(App\Livewire\Trainee\Config\TraineeFormConfig::MIN_AGE)->format('Y-m-d') }}"
            >
            @error('dob')
                <small class="error-message">{{ $message }}</small>
            @else
                <small class="note">تاريخ الميلاد للتحقق</small>
            @enderror
        </label>
    </div>


    <!-- Reset/New Search Button -->
    <div style="margin-top: 1rem; text-align: left;">
        @if($nationalIdReadonly || $statusMessage)
            <button type="button" wire:click="resetForm" class="btn btn-secondary">
                <span class="icon">🔄</span> بحث جديد
            </button>
        @endif
    </div>
</fieldset>
