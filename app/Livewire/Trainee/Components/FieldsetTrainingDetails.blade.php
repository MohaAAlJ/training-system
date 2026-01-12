@vite(['app/Livewire/Trainee/Components/fieldset-training-details.css'])

<!-- FIELDSET 3: Training Details (shown conditionally) -->
@if ($showPersonalDetails)
<fieldset class="fieldset" wire:transition>
    <legend>
        <span class="legend-icon">📚</span>بيانات التدريب
    </legend>
    <div class="grid three">
        <!-- University-specific fields -->
        @if ($trainingType === 1)
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
                <span>التخصص *</span>
                <select wire:model.live="majorId" class="form__input" required x-data="{ allMajors: @js($allMajors->toArray()) }">
                    <option value="">-- اختر --</option>
                    <template x-for="major in allMajors.filter(m => m.institutionIds && m.institutionIds.length > 0 && m.institutionIds.includes(parseInt($wire.institutionId) || 0))" :key="major.id">
                        <option :value="major.id" x-text="major.name"></option>
                    </template>
                </select>
                <small class="note">اختر التخصص</small>
            </label>
        @endif

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
            <select wire:model.live="departmentId" class="form__input" required x-data="{
                allSections: @js($allSections->toArray()),
                allDepartments: @js($allDepartments->toArray()),
                get filteredDepartments() {
                    if (!this.$wire.administrativeId) return [];
                    const deptIds = [...new Set(
                        this.allSections
                            .filter(s => s.administrativeId == this.$wire.administrativeId)
                            .map(s => s.departmentId)
                    )];
                    return this.allDepartments.filter(d => deptIds.includes(d.id));
                }
            }">
                <option value="">-- اختر --</option>
                <template x-for="dept in filteredDepartments" :key="dept.id">
                    <option :value="dept.id" x-text="dept.name"></option>
                </template>
            </select>
            <small class="note">اختر القسم</small>
        </label>

        <!-- Section/Specialization -->
        <label class="field">
            <span>التخصص *</span>
            <select wire:model.live="sectionId" class="form__input" required x-data="{
                allSections: @js($allSections->toArray())
            }">
                <option value="">-- اختر --</option>
                <template x-for="section in allSections.filter(s => 
                    s.administrativeId == ($wire.administrativeId || 0) && 
                    s.departmentId == ($wire.departmentId || 0)
                )" :key="section.id">
                    <option :value="section.id" :disabled="section.isFull === true" x-text="`${section.name} ${section.isFull ? '(ممتلئ)' : ''}`"></option>
                </template>
            </select>
            <small class="note">اختر التخصص</small>
        </label>
    </div>
</fieldset>
@endif
