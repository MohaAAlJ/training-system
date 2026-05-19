<link rel="stylesheet" href="{{ asset('css/fieldset-personal-details.css') }}">

<!-- FIELDSET 2: Personal Details (shown conditionally) -->
@if ($this->showPersonalDetails)
<fieldset class="fieldset" wire:key="fieldset-personal">
    <legend>
        <span class="legend-icon">👤</span>البيانات الشخصية
    </legend>
    <div class="grid three">
        <!-- Full Name -->
        <label class="field">
            <span>الاسم الكامل *</span>
            <input
                type="text"
                wire:model="fullName"
                wire:blur="validateField('fullName')"
                placeholder="الاسم الكامل"
                class="form__input @error('fullName') form__input--error @enderror"
                required
                @readonly($fullNameReadonly)
                :disabled="$wire.fullNameReadonly"
                @input="$el.value = $el.value.replace(/[^a-zA-Zء-ي\s]/g, '')"
                tabindex="4"
            >
            @error('fullName')
                <small class="error-message">{{ $message }}</small>
            @else
                <small class="note">الاسم الكامل</small>
            @enderror
        </label>
        </label>

        <!-- Gender -->
        <label class="field">
            <span>الجنس *</span>
            <select wire:model.live="gender" class="form__input" required tabindex="5">
                <option value="">-- اختر --</option>
                @foreach (\App\Enums\Gender::cases() as $gender)
                    <option value="{{ $gender->value }}">{{ $gender->getLabel() }}</option>
                @endforeach
            </select>
            @error('gender')
                <small class="error-message">{{ $message }}</small>
            @else
                <small class="note">الجنس</small>
            @enderror
        </label>


        <!-- Phone Number -->
        <label class="field">
            <span>رقم الجوال *</span>
            <input
                type="tel"
                inputmode="numeric"
                pattern="[0-9]*"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                maxlength="12"
                wire:model="phoneNumber"
                wire:blur="validateField('phoneNumber')"
                placeholder="9705XXXXXXXX"
                class="form__input @error('phoneNumber') form__input--error @enderror"
                required
                tabindex="6"
            >
            @error('phoneNumber')
                <small class="error-message">{{ $message }}</small>
            @else
                <small class="note">9705XXXXXXXX أو 9725XXXXXXXX (12 رقم)</small>
            @enderror
        </label>

        <!-- Governorate -->
        <label class="field">
            <span>المحافظة *</span>
            <select wire:model.live="governorateId" class="form__input" required tabindex="7">
                <option value="">-- اختر --</option>
                @foreach ($governorates as $gov)
                    <option value="{{ $gov['id'] }}">{{ $gov['name'] }}</option>
                @endforeach
            </select>
            <small class="note">اختر المحافظة</small>
        </label>

        <!-- Street Address -->
        <label class="field">
            <span>عنوان</span>
            <input
                type="text"
                wire:model.live="street"
                placeholder="عنوان السكن"
                class="form__input"
                tabindex="8"
            >
            <small class="note">عنوان</small>
        </label>
    </div>
</fieldset>
@endif
