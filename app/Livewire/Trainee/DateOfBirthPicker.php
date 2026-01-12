<?php

namespace App\Livewire\Trainee;

use Livewire\Component;
use Carbon\Carbon;

class DateOfBirthPicker extends Component
{
    public string $dob = '';

    public function mount($dob = null)
    {
        if ($dob) {
            $this->dob = $dob;
        } else {
            $this->dob = now()->subYears(20)->format('Y-m-d');
        }
    }

    public function updatedDob($value)
    {
        if ($value) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $value);
                $this->dob = $date->format('Y-m-d');
                // Dispatch to parent component
                $this->dispatch('update-dob-alpine', dob: $this->dob);
            } catch (\Exception $e) {
                // Invalid date
            }
        }
    }

    public function render()
    {
        return view('livewire.trainee.date-of-birth-picker');
    }
}

/*
    ========================================
    REMOVED CUSTOM CALENDAR CODE (COMMENTED OUT)
    ========================================
    
    The following code was previously used for a custom calendar picker
    but has been replaced with the native HTML5 date input for simplicity
    and better browser compatibility. Kept here for reference.

    ========================================
    PROPERTIES (Previously used):
    ========================================
    public int $selectedDay = 0;
    public int $selectedMonth = 1;
    public int $selectedYear = 0;
    public bool $showCalendar = false;

    public array $arabicMonths = [
        1 => 'يناير',
        2 => 'فبراير',
        3 => 'مارس',
        4 => 'أبريل',
        5 => 'مايو',
        6 => 'يونيو',
        7 => 'يوليو',
        8 => 'أغسطس',
        9 => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];

    ========================================
    METHODS (Previously used):
    ========================================
    
    public function mount($dob = null)
    {
        if ($dob) {
            $this->dob = $dob;
            $date = \Carbon\Carbon::parse($dob);
            $this->selectedYear = $date->year;
            $this->selectedMonth = $date->month;
            $this->selectedDay = $date->day;
        } else {
            $this->selectedYear = now()->subYears(20)->year;
            $this->selectedMonth = now()->subYears(20)->month;
            $this->selectedDay = now()->subYears(20)->day;
            $this->dob = now()->subYears(20)->format('Y-m-d');
        }
    }

    public function updatedSelectedMonth()
    {
        $this->updateDob();
    }

    public function updatedSelectedYear()
    {
        $this->updateDob();
    }

    public function updatedSelectedDay()
    {
        $this->updateDob();
    }

    public function updateDob()
    {
        if ($this->selectedDay && $this->selectedMonth && $this->selectedYear) {
            try {
                $date = \Carbon\Carbon::create($this->selectedYear, $this->selectedMonth, $this->selectedDay);
                $this->dob = $date->format('Y-m-d');
                
                // Dispatch to parent component
                $this->dispatch('update-dob-alpine', dob: $this->dob);
            } catch (\Exception $e) {
                // Invalid date combination, ignore
            }
        }
    }

    public function updatedDob($value)
    {
        if ($value) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $value);
                $this->selectedDay = (int) $date->format('d');
                $this->selectedMonth = (int) $date->format('m');
                $this->selectedYear = (int) $date->format('Y');
                $this->updateDob();
                $this->showCalendar = false;
            } catch (\Exception $e) {
                // Try parsing manual text input (d/month/y or similar formats)
                $this->parseManualDateInput($value);
            }
        }
    }

    private function parseManualDateInput($input)
    {
        // Try different formats: d/month/y, d-month-y, d/arabic-month/y, etc.
        $input = trim($input);
        
        // Replace common separators
        $input = str_replace('-', '/', $input);
        $parts = explode('/', $input);
        
        if (count($parts) !== 3) {
            return;
        }
        
        $day = trim($parts[0]);
        $monthInput = trim($parts[1]);
        $year = trim($parts[2]);
        
        // Validate day
        if (!is_numeric($day) || $day < 1 || $day > 31) {
            return;
        }
        
        // Try to parse month (number or Arabic name)
        $month = null;
        if (is_numeric($monthInput)) {
            $month = (int) $monthInput;
        } else {
            // Check if it's an Arabic month name
            $month = array_search($monthInput, $this->arabicMonths);
        }
        
        if (!$month || $month < 1 || $month > 12) {
            return;
        }
        
        // Validate year
        if (!is_numeric($year) || $year < 1950 || $year > now()->subYears(20)->year) {
            return;
        }
        
        // Try to create valid date
        try {
            $date = Carbon::createFromDate((int)$year, (int)$month, (int)$day);
            
            $this->selectedDay = (int) $day;
            $this->selectedMonth = (int) $month;
            $this->selectedYear = (int) $year;
            $this->updateDob();
            $this->showCalendar = false;
        } catch (\Exception $e) {
            // Invalid date combination (e.g., Feb 30), let validation handle it
        }
    }

    public function openCalendar()
    {
        $this->showCalendar = true;
    }

    public function closeCalendar()
    {
        $this->showCalendar = false;
    }

    public function selectDate($day)
    {
        $this->selectedDay = $day;
        $this->updateDob();
        $this->showCalendar = false;
    }

    public function getCalendarDaysProperty()
    {
        $year = $this->selectedYear;
        $month = $this->selectedMonth;
        
        $firstDay = new \DateTime("$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01");
        $lastDay = (new \DateTime("$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01"))->modify('last day of this month');
        
        $daysInMonth = (int)$lastDay->format('d');
        $startingDayOfWeek = (int)$firstDay->format('w');
        
        $minDate = now()->subYears(20);
        $days = [];
        
        // Previous month's days
        $prevLastDay = (new \DateTime("$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01"))->modify('first day of previous month')->modify('last day of this month');
        $prevLastDayNum = (int)$prevLastDay->format('d');
        
        for ($i = $startingDayOfWeek - 1; $i >= 0; $i--) {
            $days[] = [
                'day' => $prevLastDayNum - $i,
                'isCurrentMonth' => false,
                'isDisabled' => true,
            ];
        }
        
        // Current month's days
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $month, $i);
            $days[] = [
                'day' => $i,
                'isCurrentMonth' => true,
                'isDisabled' => $date > $minDate,
                'isSelected' => $i === $this->selectedDay,
            ];
        }
        
        // Next month's days
        $remainingDays = 42 - count($days);
        for ($i = 1; $i <= $remainingDays; $i++) {
            $days[] = [
                'day' => $i,
                'isCurrentMonth' => false,
                'isDisabled' => true,
            ];
        }
        
        return $days;
    }

    #[Computed]
    public function years()
    {
        $maxYear = now()->subYears(20)->year;
        $minYear = 1950;
        
        return array_reverse(range($minYear, $maxYear));
    }

    #[Computed]
    public function formattedDob()
    {
        if (!$this->dob) {
            return '';
        }

        return $this->selectedDay . '/' . $this->arabicMonths[$this->selectedMonth] . '/' . $this->selectedYear;
    }

    ========================================
    REMOVED BLADE VIEW CODE:
    ========================================
    
    The previous blade view had a full custom calendar UI with:
    - Month/Year selectors
    - Calendar day grid with 7-day week layout
    - Arabic month names
    - Selected date highlighting
    - Disabled date styling
    - Dark theme support
    
    Total removed: 249 lines of custom HTML, CSS, and Alpine.js logic
    
    Replaced with: Simple native HTML5 date input (8 lines)
    Benefits:
    - Better mobile experience (uses OS native picker)
    - Respects system locale (Arabic in this case)
    - Smaller bundle size
    - Better accessibility
    - Cross-browser compatible

    ========================================
    BLADE VIEW CODE (Merged & Commented):
    ========================================
    
    Previously in: resources/views/livewire/trainee/date-of-birth-picker.blade.php
    
    CURRENT (Simplified):
    
    <label class="field">
        <span>تاريخ الميلاد *</span>
        <input 
            type="date"
            wire:model.live="dob"
            class="form__input"
            max="{{ now()->subYears(20)->format('Y-m-d') }}"
            min="1950-01-01"
            required
        >
        @error('dob') <span class="form__error">{{ $message }}</span> @enderror
        <small class="note">يجب أن تكون عمرك 20 سنة على الأقل</small>
    </label>
    
    PREVIOUS (Removed Custom Calendar UI - 249 lines):
    
    <div class="dob-picker-container" wire:click.away="closeCalendar">
        <label class="field">
            <span>تاريخ الميلاد *</span>
            <div class="dob-input-wrapper">
                <input 
                    id="dob" 
                    type="date"
                    wire:model.blur="dob"
                    placeholder="اختر تاريخ الميلاد"
                    class="form__input dob-input"
                    @click.stop="$wire.openCalendar()"
                >
                <button 
                    type="button"
                    @click.stop="$wire.openCalendar()"
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
*/

