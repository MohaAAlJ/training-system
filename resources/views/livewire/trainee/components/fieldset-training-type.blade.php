<link rel="stylesheet" href="{{ asset('css/fieldset-training-type.css') }}">

<!-- FIELDSET 1: Training Type & Basic Info -->
<fieldset class="fieldset">
    <legend>
        <span class="legend-icon">🔍</span>التحقق من الطلب
    </legend>
    <div class="grid three">
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
                wire:model.live="nationalId"
                pattern="[0-9]*"
                placeholder="رقم الهوية"
                maxlength="9"
                class="form__input @error('nationalId') form__input--error @enderror"
                required
                @readonly($nationalIdReadonly)
                :disabled="$wire.isValidating"
            >
            @error('nationalId')
                <small class="error-message">{{ $message }}</small>
            @else
                <small class="note">رقم الهوية (9 أرقام)</small>
            @enderror
        </label>

        <!-- Verification Status / Button -->
        <div class="field" style="display: flex; align-items: flex-end; justify-content: center;">
            @if ($isValidating)
                <div style="text-align: center; width: 100%;">
                    <small style="color: #1976d2; font-weight: 600;">جاري التحقق...</small>
                    <div style="margin-top: 8px; height: 2px; background: linear-gradient(90deg, #1976d2, transparent); animation: pulse 1.5s infinite;"></div>
                </div>
            @elseif (strlen($nationalId) === 9 && $trainingType && $showPersonalDetails)
                <div style="text-align: center; width: 100%;">
                    <small style="color: #4caf50; font-weight: 600;">✓ تم التحقق بنجاح</small>
                </div>
            @elseif (strlen($nationalId) === 9 && $trainingType)
                <button 
                    type="button" 
                    wire:click="validateAndProceed" 
                    class="form__input" 
                    style="cursor: pointer; background: #1976d2; color: white; border: none; padding: 12px; border-radius: 4px; font-weight: 600; font-size: 14px; width: 100%; margin-top: 20px;"
                >
                    تحقق من الطلب
                </button>
            @endif
        </div>
    </div>
</fieldset>
