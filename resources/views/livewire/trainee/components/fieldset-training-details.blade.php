<link rel="stylesheet" href="{{ asset('css/fieldset-training-details.css') }}">

<!-- FIELDSET 3: Training Details (shown conditionally) -->
@if ($showTrainingDetails)
    <fieldset class="fieldset" wire:transition>
        <legend>
            <span class="legend-icon">📚</span>بيانات التدريب
        </legend>
        <div class="grid three"
             x-data="{
                adminId: @entangle('administrativeId'),
                deptId: @entangle('departmentId'),
                sectId: @entangle('sectionId'),
                instId: @entangle('institutionId'),
                majId: @entangle('majorId'),

                allAdmins: {{ json_encode($administratives) }},
                allDepts: {{ json_encode($allDepartments) }},
                allSections: {{ json_encode($allSections) }},
                allInstitutions: {{ json_encode($institutions) }},
                allMajors: {{ json_encode($allMajors) }},

                get availableDepartments() {
                    if (!this.adminId) return [];
                    const validDeptIds = new Set(
                        this.allSections
                            .filter(s => s.administrativeId == this.adminId)
                            .map(s => s.departmentId)
                    );
                    return this.allDepts.filter(d => validDeptIds.has(d.id));
                },
                get availableSections() {
                    if (!this.adminId || !this.deptId) return [];
                    return this.allSections.filter(s =>
                        s.administrativeId == this.adminId &&
                        s.departmentId == this.deptId
                    );
                },
                get availableMajors() {
                    if (!this.instId) return [];
                    const selectedInstId = Number(this.instId);
                    // Filter majors that belong to the selected institution
                    // Using loose comparison since IDs might be strings or numbers
                    return this.allMajors.filter(major => {
                        if (!major.institutionIds || !Array.isArray(major.institutionIds)) return false;
                        return major.institutionIds.some(id => Number(id) === selectedInstId);
                    });
                }
             }"
        >
            <!-- University-specific fields -->
            @if ($isUniversity)
                <label class="field">
                    <span>مؤسسة تعليمية *</span>
                    <select x-model="instId" @change="majId = null" class="form__input" required tabindex="9">
                        <option value="">-- اختر --</option>
                        <template x-for="inst in allInstitutions" :key="inst.id">
                            <option :value="inst.id" x-text="inst.name"></option>
                        </template>
                    </select>
                    <small class="note">اختر المؤسسة التعليمية</small>
                </label>

                <label class="field">
                    <span>التخصص الجامعي *</span>
                    <select x-model="majId" class="form__input" required :disabled="availableMajors.length === 0" tabindex="10">
                        <option value="">-- اختر --</option>
                        <template x-for="major in availableMajors" :key="major.id">
                            <option :value="major.id" x-text="major.name"></option>
                        </template>
                    </select>
                    <small class="note" x-show="availableMajors.length > 0">اختر التخصص</small>
                    <small class="note note--warning">إذا لم تجد تخصصك الجامعي راجع كليتك، أو راسلنا عبر الواتساب</small>
                </label>


                <label class="field">
                    <span>الرقم الجامعي *</span>
                    <input type="text" wire:model.blur="universityNumber" placeholder="أدخل رقمك الجامعي" class="form__input"
                        required tabindex="11">
                    <small class="note">الرقم الجامعي (مطلوب للطلاب)</small>
                </label>
            @endif


            <!-- Administrative Location -->
            <label class="field">
                <span>مكان التدريب *</span>
                <select x-model="adminId" @change="deptId = null; sectId = null;" class="form__input" required :disabled="allAdmins.length === 0" tabindex="12">
                    <option value="">-- اختر --</option>
                    <template x-for="admin in allAdmins" :key="admin.id">
                        <option :value="admin.id" x-text="admin.name"></option>
                    </template>
                </select>
                <small class="note" x-show="allAdmins.length > 0">اختر مكان التدريب</small>
                <small class="note note--warning" x-show="allAdmins.length === 0">جاري تحميل البيانات...</small>
            </label>

            <!-- Department -->
            <label class="field">
                <span>القسم *</span>
                <select x-model="deptId" @change="sectId = null" class="form__input" required :disabled="availableDepartments.length === 0" tabindex="13">
                    <option value="">-- اختر --</option>
                    <template x-for="dept in availableDepartments" :key="dept.id">
                        <option :value="dept.id" x-text="dept.name"></option>
                    </template>
                </select>
                <small class="note" x-show="availableDepartments.length > 0">اختر القسم</small>
                <small class="note note--warning" x-show="adminId && availableDepartments.length === 0">لا توجد أقسام متاحة</small>
            </label>

            <!-- Section/Specialization -->
            <label class="field">
                <span>تخصص التدريب *</span>
                <select x-model="sectId" class="form__input" required :disabled="availableSections.length === 0" tabindex="14">
                    <option value="">-- اختر --</option>
                    <template x-for="sec in availableSections" :key="sec.id">
                        <option :value="sec.id" :disabled="sec.isFull" x-text="sec.name + (sec.isFull ? ' (ممتلئ)' : '')"></option>
                    </template>
                </select>
                <small class="note" x-show="availableSections.length > 0">اختر التخصص</small>
                <small class="note note--warning" x-show="deptId && availableSections.length === 0">لا توجد تخصصات متاحة</small>
            </label>
            <!-- Training Hours -->
            <label class="field">
                <span>عدد ساعات التدريب *</span>
                <input type="number" wire:model.blur="trainingHours" placeholder="50 - 1000 ساعة" min="50" max="1000"
                    class="form__input" required tabindex="15">
                <small class="note">عدد ساعات التدريب بين 50 و 1000</small>
            </label>

        </div>
    </fieldset>
@endif
