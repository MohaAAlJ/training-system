<div class="dob-picker-container">
    <label class="field">
        <span>تاريخ الميلاد *</span>
        <div class="dob-input-wrapper">
            <input 
                id="dob" 
                type="date"
                wire:model.blur="dob"
                placeholder="اختر تاريخ الميلاد"
                class="form__input dob-input"
                @click="$wire.toggleCalendar()"
            >
            <button 
                type="button"
                @click="$wire.toggleCalendar()"
                class="dob-calendar-btn"
                title="فتح التقويم"
            >
                📅
            </button>
        </div>
        @error('dob') <span class="form__error">{{ $message }}</span> @enderror
        <small class="note">يجب أن تكون عمرك 20 سنة على الأقل</small>
    </label>

    @if ($showCalendar)
    <div class="datepicker-calendar" @click.stop>
        <div class="calendar-controls">
            <select 
                wire:model.live="selectedMonth" 
                class="month-select"
            >
                @foreach ($arabicMonths as $num => $name)
                    <option value="{{ $num }}">{{ $name }}</option>
                @endforeach
            </select>

            <select 
                wire:model.live="selectedYear" 
                class="year-select"
            >
                @foreach ($this->years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="calendar-weekdays">
            <div class="weekday">ح</div>
            <div class="weekday">ن</div>
            <div class="weekday">ث</div>
            <div class="weekday">ر</div>
            <div class="weekday">خ</div>
            <div class="weekday">ج</div>
            <div class="weekday">س</div>
        </div>

        <div class="calendar-days">
            @foreach ($this->calendarDays as $index => $day)
                <button 
                    type="button"
                    wire:click.stop="selectDate({{ $day['day'] }})"
                    @class([
                        'calendar-day',
                        'other-month' => !$day['isCurrentMonth'],
                        'disabled' => $day['isDisabled'],
                        'selected' => $day['isSelected'] ?? false,
                    ])
                    @disabled($day['isDisabled'])
                >
                    {{ $day['day'] }}
                </button>
            @endforeach
        </div>
    </div>
    @endif

    <style>
        .dob-picker-container {
            position: relative;
        }

        .dob-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .dob-input {
            width: 100%;
            padding-right: 40px;
        }

        /* Hide native browser calendar icon */
        .dob-input::-webkit-calendar-picker-indicator {
            display: none;
        }

        .dob-calendar-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 4px 8px;
            transition: transform 0.2s;
        }

        .dob-calendar-btn:hover {
            transform: scale(1.1);
        }

        [data-theme="dark"] .dob-calendar-btn {
            color: #fff;
        }

        .datepicker-calendar {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            background: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            min-width: 320px;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        [data-theme="dark"] .datepicker-calendar {
            background: #2a2a2a;
            border-color: #444;
        }

        .calendar-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            justify-content: center;
        }

        .month-select,
        .year-select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            color: #333;
            cursor: pointer;
            flex: 1;
            min-width: 120px;
        }

        [data-theme="dark"] .month-select,
        [data-theme="dark"] .year-select {
            background: #3a3a3a;
            color: #fff;
            border-color: #555;
        }

        .month-select:focus,
        .year-select:focus {
            outline: none;
            border-color: #2196f3;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 12px;
        }

        .weekday {
            padding: 5px;
            color: #666;
        }

        [data-theme="dark"] .weekday {
            color: #999;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day {
            padding: 8px;
            border: 1px solid #e0e0e0;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.2s;
        }

        [data-theme="dark"] .calendar-day {
            background: #3a3a3a;
            border-color: #555;
            color: #fff;
        }

        .calendar-day:hover:not(.disabled) {
            background: #e3f2fd;
            border-color: #2196f3;
        }

        [data-theme="dark"] .calendar-day:hover:not(.disabled) {
            background: #004d99;
            border-color: #2196f3;
        }

        .calendar-day.selected {
            background: #2196f3;
            color: white;
            border-color: #2196f3;
            font-weight: 600;
        }

        .calendar-day.other-month {
            color: #ccc;
            cursor: default;
        }

        [data-theme="dark"] .calendar-day.other-month {
            color: #555;
        }

        .calendar-day.disabled {
            cursor: not-allowed;
            opacity: 0.5;
            background: #f5f5f5;
        }

        [data-theme="dark"] .calendar-day.disabled {
            background: #2a2a2a;
        }
    </style>
</div>
