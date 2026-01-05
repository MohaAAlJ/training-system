<?php

namespace App\Filament\Widgets;

use App\Models\User;


use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class GTMCapacityChart extends ChartWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 1;
    protected ?string $maxHeight = '260px';
    protected ?string $heading = 'توزيع السعة الاستيعابية';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return in_array($user->role, [
            User::ROLE_GTM,
            User::ROLE_ADMIN,
            User::ROLE_HOA,
            User::ROLE_HOM,
            User::ROLE_DEPARTMENT,
            User::ROLE_SECTION,
        ]);
    }

    protected function getData(): array
    {
        $user = Auth::user();

        if ($user->isGeneralTrainingManager() || $user->isAdmin()) {
            // Aggregate from all sections using a single query
            $total = (int) \App\Models\Section::sum('capacity');
            $used = \App\Models\Application::where('status', \App\Models\Application::STATUS_STARTED_TRAINING)->count();
            $available = max(0, $total - $used);

            $stats = [
                'total' => $total,
                'used' => $used,
                'available' => $available
            ];
        } elseif ($user->isMedicalManager()) { // HOM
            $total = (int) \App\Models\Section::whereHas('department', fn($q) => $q->where('is_medical', true))
                ->sum('capacity');
            $used = \App\Models\Application::whereHas('section.department', fn($q) => $q->where('is_medical', true))
                ->where('status', \App\Models\Application::STATUS_STARTED_TRAINING)
                ->count();
            $available = max(0, $total - $used);

            $stats = [
                'total' => $total,
                'used' => $used,
                'available' => $available
            ];
        } elseif ($user->isHOA()) { // HOA
            $adminUnit = \App\Models\Administrative::where('user_id', $user->id)->first();
            $stats = $adminUnit ? $adminUnit->getCapacityStats() : ['total' => 0, 'used' => 0, 'available' => 0];
        } elseif ($user->isDepartmentHead()) {
            $dept = \App\Models\Department::where('user_id', $user->id)->first();
            $stats = $dept ? $dept->getCapacityStats() : ['total' => 0, 'used' => 0, 'available' => 0];
        } elseif ($user->isSectionHead()) {
            $section = \App\Models\Section::where('user_id', $user->id)->first();
            $stats = $section ? $section->getCapacityStats() : ['total' => 0, 'used' => 0, 'available' => 0];
        } else {
            $stats = ['total' => 0, 'used' => 0, 'available' => 0];
        }

        $total = $stats['total'];
        $used = $stats['used'];
        $available = max(0, $total - $used);

        $available = max(0, $total - $used);

        return [
            'datasets' => [
                [
                    'label' => 'السعة',
                    'data' => [$used, $available],
                    'backgroundColor' => [
                        '#f4600d',
                        '#2279c5',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['مشغول', 'متاح'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'boxWidth' => 8,
                        'font' => ['size' => 10]
                    ],
                ],
            ],
            'cutout' => '70%',
        ];
    }
}
