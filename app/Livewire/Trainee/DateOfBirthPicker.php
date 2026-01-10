<?php

namespace App\Livewire\Trainee;

use Livewire\Component;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class DateOfBirthPicker extends Component
{
    public string $dob = '';
    public int $selectedDay = 0;
    public int $selectedMonth = 1;
    public int $selectedYear = 0;
    public bool $showCalendar = true;

    // Arabic month names
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

    public function toggleCalendar()
    {
        $this->showCalendar = !$this->showCalendar;
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

    public function render()
    {
        return view('livewire.trainee.date-of-birth-picker');
    }
}
