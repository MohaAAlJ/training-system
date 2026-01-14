<link rel="stylesheet" href="{{ asset('css/fieldset-training-details.css') }}">

<!-- FIELDSET 3: Training Details (shown conditionally) -->
@if ($showPersonalDetails)
<fieldset class="fieldset" wire:transition>
    <legend>
        <span class="legend-icon">📚</span>بيانات التدريب
    </legend>
    <div class="grid three">
        <!-- University-specific fields -->
        @if ($isUniversity)
            <label class="field">
                <span>مؤسسة تعليمية *</span>
                <select wire:model.live="institutionId" class="form__input" required>
                    <option value="">-- اختر --</option>
                    @foreach ($institutions as $inst)
                        <option value="{{ $inst['id'] }}">{{ $inst['name'] }}</option>
                    @endforeach
                </select>
                <small class="note">اختر المؤسسة التعليمية</small>
            </label>

            <label class="field">
                <span>التخصص الجامعي *</span>
                <select wire:model.live="majorId" class="form__input" required @disabled($majors->isEmpty())>
                    <option value="">-- اختر --</option>
                    @foreach ($majors as $major)
                        <option value="{{ $major['id'] }}">{{ $major['name'] }}</option>
                    @endforeach
                </select>
                <small class="note">اختر التخصص</small>
            </label>
        @endif

        
        <!-- Administrative Location -->
        <label class="field">
            <span>مكان التدريب *</span>
            <select wire:model.live="administrativeId" class="form__input" required>
                <option value="">-- اختر --</option>
                @foreach ($administratives as $admin)
                    <option value="{{ $admin['id'] }}">{{ $admin['name'] }}</option>
                @endforeach
            </select>
            <small class="note">اختر مكان التدريب</small>
        </label>

        <!-- Department -->
        <label class="field">
            <span>القسم *</span>
            <select wire:model.live="departmentId" class="form__input" required @disabled($departments->isEmpty())>
                <option value="">-- اختر --</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                @endforeach
                @if ($departments->isEmpty() && $administrativeId)
                    <option disabled>لا توجد أقسام متاحة</option>
                @endif
            </select>
            <small class="note">اختر القسم</small>
        </label>

        <!-- Section/Specialization -->
        <label class="field">
            <span>تخصص التدريب  *</span>
            <select wire:model.live="sectionId" class="form__input" required @disabled($sections->isEmpty())>
                <option value="">-- اختر --</option>
                @foreach ($sections as $section)
                    <option value="{{ $section['id'] }}" @disabled($section['isFull'] ?? false)>
                        {{ $section['name'] }} {{ ($section['isFull'] ?? false) ? '(ممتلئ)' : '' }}
                    </option>
                @endforeach
                @if ($sections->isEmpty() && $departmentId)
                    <option disabled>لا توجد تخصصات متاحة</option>
                @endif
            </select>
            <small class="note">اختر التخصص</small>
        </label>
        <!-- Training Hours -->
        <label class="field">
            <span>عدد ساعات التدريب *</span>
            <input 
                type="number" 
                wire:model.live="trainingHours" 
                placeholder="50 - 1000 ساعة"
                min="50"
                max="1000"
                class="form__input"
                required
            >
            <small class="note">عدد ساعات التدريب بين 50 و 1000</small>
        </label>

    </div>
</fieldset>
@endif

