<link rel="stylesheet" href="{{ asset('css/fieldset-training-details.css') }}">

<!-- FIELDSET 3: Training Details (shown conditionally) -->
@if ($this->showTrainingDetails)
    @php
        $administrativeGroups = collect($governorates)
            ->map(function (array $governorate) use ($administratives) {
                $items = collect($administratives)
                    ->filter(fn (array $admin) => (int) ($admin['governorate_id'] ?? 0) === (int) $governorate['id'])
                    ->sortBy('name')
                    ->values();

                return [
                    'id' => $governorate['id'],
                    'name' => $governorate['name'],
                    'items' => $items,
                ];
            })
            ->filter(fn (array $group) => $group['items']->isNotEmpty())
            ->values();

        $selectedAdministrative = collect($administratives)
            ->first(fn (array $admin) => (int) $admin['id'] === (int) $administrativeId);
    @endphp

    <fieldset class="fieldset" wire:key="fieldset-training">
        <legend>
            <span class="legend-icon">📚</span>بيانات التدريب
        </legend>
        <div class="grid three">
            <!-- University-specific fields -->
            @if ($isUniversity)
                <label class="field">
                    <span>مؤسسة تعليمية *</span>
                    <select wire:model.live="institutionId" class="form__input" required tabindex="9">
                        <option value="">-- اختر --</option>
                        @foreach($institutions as $inst)
                            <option value="{{ $inst['id'] }}">{{ $inst['name'] }}</option>
                        @endforeach
                    </select>
                    <small class="note">اختر المؤسسة التعليمية</small>
                </label>

                <label class="field">
                    <span>التخصص الجامعي *</span>
                    <select wire:model.live="majorId" class="form__input" required @disabled(empty($majors)) tabindex="10">
                        <option value="">-- اختر --</option>
                        @foreach($majors as $major)
                            <option value="{{ $major['id'] }}">{{ $major['name'] }}</option>
                        @endforeach
                    </select>
                    @if(!empty($majors))
                        <small class="note">اختر التخصص</small>
                    @endif
                    <small class="note note--warning">إذا لم تجد تخصصك الجامعي راجع كليتك، أو راسلنا عبر الواتساب</small>
                </label>

                <label class="field">
                    <span>الرقم الجامعي *</span>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" wire:model.blur="universityNumber" placeholder="أدخل رقمك الجامعي" class="form__input"
                        required tabindex="11">
                    <small class="note">الرقم الجامعي</small>
                </label>
            @endif

            <!-- Administrative Location -->
            <div class="field admin-accordion-field"
                x-data="{
                    open: false,
                    panelStyle: '',
                    expandedGovernorateId: @js($selectedAdministrative['governorate_id'] ?? ($administrativeGroups->first()['id'] ?? null)),
                    toggleOpen() {
                        if (this.open) {
                            this.closePanel();
                            return;
                        }

                        this.open = true;
                        this.$nextTick(() => this.updatePanelPosition());
                    },
                    closePanel() {
                        this.open = false;
                    },
                    updatePanelPosition() {
                        if (!this.open || !this.$refs.trigger) {
                            return;
                        }

                        const rect = this.$refs.trigger.getBoundingClientRect();
                        const gap = 8;
                        const viewportPadding = 12;
                        const availableBelow = window.innerHeight - rect.bottom - gap - viewportPadding;
                        const maxHeight = Math.max(220, availableBelow);

                        this.panelStyle = [
                            'position: fixed',
                            'top: ' + Math.round(rect.bottom + gap) + 'px',
                            'left: ' + Math.round(rect.left) + 'px',
                            'width: ' + Math.round(rect.width) + 'px',
                            'max-height: ' + Math.round(maxHeight) + 'px',
                        ].join('; ');
                    },
                    handleWindowClick(event) {
                        if (!this.open) {
                            return;
                        }

                        if (this.$refs.trigger?.contains(event.target) || this.$refs.panel?.contains(event.target)) {
                            return;
                        }

                        this.closePanel();
                    },
                    toggleGovernorate(id) {
                        this.expandedGovernorateId = this.expandedGovernorateId === id ? null : id;
                    },
                    chooseAdministrative(id, governorateId) {
                        this.expandedGovernorateId = governorateId;
                        this.closePanel();
                        $wire.set('administrativeId', id);
                    }
                }"
                @click.window="handleWindowClick($event)"
                @keydown.escape.window="closePanel()"
                @resize.window="if (open) updatePanelPosition()"
                @scroll.window="if (open) updatePanelPosition()">
                <span>مكان التدريب *</span>

                <button type="button"
                    x-ref="trigger"
                    class="form__input admin-accordion-trigger"
                    :class="{ 'admin-accordion-trigger--open': open }"
                    @click="toggleOpen()"
                    @disabled(empty($administratives))
                    :aria-expanded="open ? 'true' : 'false'"
                    aria-haspopup="listbox"
                    tabindex="12">
                    <span class="admin-accordion-trigger__text">
                        @if($selectedAdministrative)
                            <span class="admin-accordion-trigger__label">{{ $selectedAdministrative['name'] }}</span>
                            <span class="admin-accordion-trigger__meta">{{ $selectedAdministrative['governorate_name'] ?? '' }}</span>
                        @else
                            <span class="admin-accordion-trigger__placeholder">-- اختر مكان التدريب --</span>
                        @endif
                    </span>
                    <span class="admin-accordion-trigger__icon" :class="{ 'admin-accordion-trigger__icon--open': open }" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>

                <template x-teleport="body">
                    <div x-show="open" x-cloak x-ref="panel" class="admin-accordion-panel" :style="panelStyle" role="listbox">
                        @foreach($administrativeGroups as $group)
                            <div class="admin-accordion-group">
                                <button type="button"
                                    class="admin-accordion-group__toggle"
                                    :class="{ 'admin-accordion-group__toggle--open': expandedGovernorateId === {{ $group['id'] }} }"
                                    @click="toggleGovernorate({{ $group['id'] }})"
                                    :aria-expanded="expandedGovernorateId === {{ $group['id'] }} ? 'true' : 'false'">
                                    <span>{{ $group['name'] }}</span>
                                    <span class="admin-accordion-group__count">{{ $group['items']->count() }}</span>
                                </button>

                                <div x-show="expandedGovernorateId === {{ $group['id'] }}" class="admin-accordion-group__items">
                                    @foreach($group['items'] as $admin)
                                        <button type="button"
                                            @class([
                                                'admin-accordion-item',
                                                'admin-accordion-item--selected' => (int) $administrativeId === (int) $admin['id'],
                                            ])
                                            @click="chooseAdministrative({{ $admin['id'] }}, {{ $group['id'] }})">
                                            <span class="admin-accordion-item__main">
                                                <span class="admin-accordion-item__name">{{ $admin['name'] }}</span>
                                                @if(!empty($admin['address']))
                                                    <span class="admin-accordion-item__address">{{ $admin['address'] }}</span>
                                                @endif
                                            </span>
                                            @if((int) $administrativeId === (int) $admin['id'])
                                                <span class="admin-accordion-item__check" aria-hidden="true">
                                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </template>

                @if($selectedAdministrative)
                    <small class="note">{{ $selectedAdministrative['governorate_name'] ?? '' }}</small>
                    @if(!empty($selectedAdministrative['address']))
                        <small class="note admin-address-note">{{ $selectedAdministrative['address'] }}</small>
                    @endif
                @elseif(!empty($administratives))
                    <small class="note">اختر مكان التدريب</small>
                @else
                    <small class="note note--warning">جاري تحميل البيانات...</small>
                @endif
            </div>

            <!-- Department -->
            <label class="field">
                <span>القسم *</span>
                <select wire:model.live="departmentId" class="form__input" required @disabled(empty($departments))
                    tabindex="13">
                    <option value="">-- اختر --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                    @endforeach
                </select>
                @if(!empty($departments))
                    <small class="note">اختر القسم</small>
                @elseif($administrativeId)
                    <small class="note note--warning">لا توجد أقسام متاحة</small>
                @endif
            </label>

            <!-- Section/Specialization -->
            <label class="field">
                <span>تخصص التدريب *</span>
                <select wire:model.live="sectionId" class="form__input" required @disabled(empty($sections)) tabindex="14">
                    <option value="">-- اختر --</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec['id'] }}" @disabled($sec['isFull'] ?? false)>
                            {{ $sec['name'] }} {{ ($sec['isFull'] ?? false) ? '(ممتلئ)' : '' }}
                        </option>
                    @endforeach
                </select>
                @if(!empty($sections))
                    <small class="note">اختر التخصص</small>
                @elseif($departmentId)
                    <small class="note note--warning">لا توجد تخصصات متاحة</small>
                @endif
            </label>

            <!-- Training Hours -->
            <label class="field">
                <span>عدد ساعات التدريب *</span>
                <input type="text" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="3" wire:model.blur="trainingHours" placeholder="50 - 1000 ساعة"
                    class="form__input" required tabindex="15">
                <small class="note">عدد ساعات التدريب بين 50 و 1000</small>
            </label>

        </div>
    </fieldset>
@endif
