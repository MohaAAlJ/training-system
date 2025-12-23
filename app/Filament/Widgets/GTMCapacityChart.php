<?php

namespace App\Filament\Widgets;

use App\Helpers\Constans;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class GTMCapacityChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'توزيع السعة الاستيعابية';
    protected ?string $maxHeight = '250px';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return in_array($user->role, [
            Constans::ROLE_GTM,
            Constans::ROLE_ADMIN,
            Constans::ROLE_HOA,
            Constans::ROLE_HOM,
            Constans::ROLE_DEPARTMENT,
            Constans::ROLE_SECTION,
        ]);
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $total = 0;
        $used = 0;

        if ($user->isGeneralTrainingManager() || $user->isAdmin()) {
            $stats = Constans::getCapacityStats();
            $total = $stats['total'];
            $used = $stats['used'];
        } elseif ($user->isMedicalManager()) { // HOM
            // Filter by Medical Departments
            $total = \App\Models\Sections::whereHas('department', fn($q) => $q->where('is_medical', true))->sum('total_capacity');
            $used = \App\Models\Applications::where('status', Constans::STATUS_STRATED_TRAINING)
                ->whereHas('department', fn($q) => $q->where('is_medical', true))
                ->count();
        } elseif ($user->isHOA()) { // HOA
             $stats = Constans::getCapacityStats($user->administrative?->id);
             $total = $stats['total'];
             $used = $stats['used'];
        } elseif ($user->isDepartmentHead()) {
             $stats = Constans::getCapacityStats(null, $user->department?->id);
             $total = $stats['total'];
             $used = $stats['used'];
        } elseif ($user->isSectionHead()) {
             $stats = Constans::getCapacityStats(null, null, $user->sections?->id);
             $total = $stats['total'];
             $used = $stats['used'];
        }

        $available = max(0, $total - $used);

        return [
            'datasets' => [
                [
                    'label' => 'السعة',
                    'data' => [$used, $available],
                    'backgroundColor' => [
                        '#f46a0fff',
                        '#2279c5ff',
                    ],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => ['مشغول', 'متاح'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
