<link rel="stylesheet" href="{{ asset('css/fieldset-training-details.css') }}">

<!-- FIELDSET 3: Training Details (shown conditionally) -->
@if ($showPersonalDetails)
<fieldset class="fieldset" wire:transition x-data="trainingDetailsData()" @wire:ignore.self>
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
                <select wire:model.live="majorId" class="form__input" required>
                    <option value="">-- اختر --</option>
                    <template x-for="major in filterMajors()" :key="major.id">
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
            <select wire:model.live="departmentId" class="form__input" required :disabled="getFilteredDepartments().length === 0 && $wire.administrativeId">
                <option value="">-- اختر --</option>
                <template x-for="dept in getFilteredDepartments()" :key="dept.id">
                    <option :value="dept.id" x-text="dept.name"></option>
                </template>
                <template x-if="getFilteredDepartments().length === 0 && $wire.administrativeId">
                    <option disabled>لا توجد أقسام متاحة</option>
                </template>
            </select>
            <small class="note">اختر القسم</small>
        </label>

        <!-- Section/Specialization -->
        <label class="field">
            <span>التخصص *</span>
            <select wire:model.live="sectionId" class="form__input" required :disabled="getFilteredSections().length === 0 && $wire.departmentId">
                <option value="">-- اختر --</option>
                <template x-for="section in getFilteredSections()" :key="section.id">
                    <option :value="section.id" :disabled="section.isFull === true" x-text="`${section.name} ${section.isFull ? '(ممتلئ)' : ''}`"></option>
                </template>
                <template x-if="getFilteredSections().length === 0 && $wire.departmentId">
                    <option disabled>لا توجد تخصصات متاحة</option>
                </template>
            </select>
            <small class="note">اختر التخصص</small>
        </label>
    </div>
</fieldset>
@endif

@script
<script>
function trainingDetailsData() {
    return {
        allMajors: @js($allMajors->toArray()),
        allSections: @js($allSections->toArray()),
        allDepartments: @js($allDepartments->toArray()),
        isReady: false,
        
        init() {
            // Mark as ready after Alpine is initialized
            this.isReady = true;
            
            // Watch for changes from Livewire
            this.$watch('$wire.institutionId', () => {
                // Trigger re-render of majorId select
                this.$nextTick(() => {});
            });
            
            this.$watch('$wire.administrativeId', () => {
                // Reset dependent fields and trigger re-render
                this.$nextTick(() => {});
            });
            
            this.$watch('$wire.departmentId', () => {
                // Trigger re-render of sectionId select
                this.$nextTick(() => {});
            });
        },
        
        filterMajors() {
            if (!this.isReady) return [];
            try {
                const institutionId = parseInt(this.$wire?.institutionId || 0);
                if (!institutionId || !Array.isArray(this.allMajors)) return [];
                return this.allMajors.filter(m => 
                    m && m.institutionIds && 
                    m.institutionIds.length > 0 && 
                    m.institutionIds.includes(institutionId)
                );
            } catch (e) {
                console.error('Error in filterMajors:', e);
                return [];
            }
        },
        
        getFilteredDepartments() {
            if (!this.isReady) return [];
            try {
                const adminId = parseInt(this.$wire?.administrativeId || 0);
                if (!adminId || !Array.isArray(this.allSections) || !Array.isArray(this.allDepartments)) return [];
                const deptIds = [...new Set(
                    this.allSections
                        .filter(s => s && s.administrativeId == adminId)
                        .map(s => s?.departmentId)
                        .filter(id => id)
                )];
                return this.allDepartments.filter(d => d && deptIds.includes(d.id));
            } catch (e) {
                console.error('Error in getFilteredDepartments:', e);
                return [];
            }
        },
        
        getFilteredSections() {
            if (!this.isReady) return [];
            try {
                const adminId = parseInt(this.$wire?.administrativeId || 0);
                const deptId = parseInt(this.$wire?.departmentId || 0);
                if (!adminId || !deptId || !Array.isArray(this.allSections)) return [];
                return this.allSections.filter(s => 
                    s && 
                    s.administrativeId == adminId && 
                    s.departmentId == deptId
                );
            } catch (e) {
                console.error('Error in getFilteredSections:', e);
                return [];
            }
        }
    };
}
</script>
@endscript
