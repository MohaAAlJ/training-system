<?php

namespace App\Filament\Widgets;

use App\Models\Administrative;
use App\Models\Application;
use App\Models\Section;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class GTMCapacityChart extends ChartWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 1;
    protected ?string $maxHeight = '260px';
    protected ?string $heading = 'توزيع السعة الاستيعابية';

    // Intentionally disabled — chart not yet in use.
    public static function canView(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        $user = Auth::user();
        if (!$user) {
            return $this->emptyChartData();
        }

        $stats = match (true) {
            $user->isTrainingManagerLike() || $user->isAdmin() => $this->calculateAdminOrGtmStats(),
            $user->isMedicalManager() => $this->calculateMedicalManagerStats($user),
            $user->isHOA() => $this->calculateHoaStats($user),
            $user->isDepartmentHead() => $this->calculateDepartmentHeadStats($user),
            $user->isSectionHead() => $this->calculateSectionHeadStats($user),
            default => ['total' => 0, 'used' => 0, 'available' => 0],
        };

        $used = $stats['used'];
        $available = max(0, $stats['total'] - $used);

        return [
            'datasets' => [
                [
                    'label' => 'السعة',
                    'data' => [$used, $available],
                    'backgroundColor' => ['#f4600d', '#2279c5'],
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
                        'font' => ['size' => 10],
                    ],
                ],
            ],
            'cutout' => '70%',
        ];
    }

    private function emptyChartData(): array
    {
        return [
            'datasets' => [['label' => 'السعة', 'data' => [0, 0], 'backgroundColor' => ['#f4600d', '#2279c5'], 'borderWidth' => 0]],
            'labels' => ['مشغول', 'متاح'],
        ];
    }

    private function calculateAdminOrGtmStats(): array
    {
        $total = (int) Section::sum('capacity');
        $used = Application::where('status', Application::STATUS_STARTED_TRAINING)->count();
        return ['total' => $total, 'used' => $used, 'available' => max(0, $total - $used)];
    }

    private function calculateMedicalManagerStats(User $user): array
    {
        $adminUnit = Administrative::where('medical_head_user_id', $user->id)->active()->first();
        if (!$adminUnit) {
            return ['total' => 0, 'used' => 0, 'available' => 0];
        }

        $total = (int) Section::where('administrative_id', $adminUnit->id)
            ->active()
            ->whereHas('departments', fn($q) => $q->where('is_medical', true)->active()->visible())
            ->sum('capacity');

        $used = Application::whereHas('section', fn($s) =>
            $s->where('administrative_id', $adminUnit->id)
              ->whereHas('departments', fn($d) => $d->where('is_medical', true)->active()->visible())
        )
            ->where('status', Application::STATUS_STARTED_TRAINING)
            ->count();

        return ['total' => $total, 'used' => $used, 'available' => max(0, $total - $used)];
    }

    private function calculateHoaStats(User $user): array
    {
        $adminUnit = $user->administrative()->active()->first();
        return $adminUnit ? $adminUnit->getCapacityStats() : ['total' => 0, 'used' => 0, 'available' => 0];
    }

    private function calculateDepartmentHeadStats(User $user): array
    {
        $dept = $user->department()->active()->first();
        return $dept ? $dept->getCapacityStats() : ['total' => 0, 'used' => 0, 'available' => 0];
    }

    private function calculateSectionHeadStats(User $user): array
    {
        $section = $user->section()->active()->first();
        return $section ? $section->getCapacityStats() : ['total' => 0, 'used' => 0, 'available' => 0];
    }
}
