<label class="field">
    <span>تاريخ الميلاد *</span>
    <input type="date" wire:model.live="dob" class="form__input" max="{{ now()->subYears(18)->format('Y-m-d') }}"
        min="1950-01-01" required>
    @error('dob') <span class="form__error">{{ $message }}</span> @enderror
    <small class="note">يجب أن يكون عمرك 18 سنة على الأقل</small>
</label>
