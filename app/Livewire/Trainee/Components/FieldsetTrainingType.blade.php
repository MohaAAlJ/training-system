@vite(['app/Livewire/Trainee/Components/fieldset-training-type.css'])

<!-- FIELDSET 1: Training Type & Basic Info -->
<fieldset class="fieldset">
    <legend>
        <span class="legend-icon">🔍</span>التحقق من الطلب
    </legend>
    <div class="grid three">
        <!-- Training Type Selection -->
        <label class="field">
            <span>نوع التدريب *</span>
            <select wire:model.live="trainingType" class="form__input" required>
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
                wire:model.live="nationalId"
                pattern="[0-9]*"
                placeholder="رقم الهوية"
                maxlength="9"
                class="form__input @error('nationalId') form__input--error @enderror"
                required
                @readonly($nationalIdReadonly)
            >
            @error('nationalId')
                <small class="error-message">{{ $message }}</small>
            @else
                <small class="note">رقم الهوية (9 أرقام)</small>
            @enderror
        </label>
    </div>
</fieldset>
