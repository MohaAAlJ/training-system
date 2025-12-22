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
        ]);
    }

    protected function getData(): array
    {
        $stats = Constans::getCapacityStats();

        return [
            'datasets' => [
                [
                    'label' => 'السعة',
                    'data' => [$stats['used'], $stats['available']],
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
