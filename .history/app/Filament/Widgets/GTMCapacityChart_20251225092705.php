<?php

namespace App\Filament\Widgets;

use App\Models\User;

use App\Helpers\Constants;
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
        $total = 0;
        $used = 0;

        if ($user->isGeneralTrainingManager() || $user->isAdmin()) {
            $stats = Constans::getCapacityStats();
        } elseif ($user->isMedicalManager()) { // HOM
            $stats = Constans::getCapacityStats(null, null, null, true);
        } elseif ($user->isHOA()) { // HOA
             $stats = Constans::getCapacityStats($user->administrative?->id);
        } elseif ($user->isDepartmentHead()) {
             $stats = Constans::getCapacityStats(null, $user->department?->id);
        } elseif ($user->isSectionHead()) {
             $stats = Constans::getCapacityStats(null, null, $user->Section?->id);
        } else {
            $stats = ['total' => 0, 'used' => 0, 'available' => 0];
        }

        $total = $stats['total'];
        $used = $stats['used'];

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





