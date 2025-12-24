<?php

namespace App\Filament\Widgets;

use App\Helpers\Constans;
use App\Models\Action;
use App\Models\Applications;
use App\Models\Departments;
use App\Models\Sections;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = [
        'md' => 1, // default or smaller
        'lg' => 1,
    ];

    public function getColumnSpan(): int | string | array
    {
        return 1;
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Hidden from: MOH (4), College Supervisor (5)
        // These roles currently have no stats logic in getStats()
        return in_array($user->role, [
            Constans::ROLE_GTM,
            Constans::ROLE_ADMIN,
            Constans::ROLE_HOA,
            Constans::ROLE_HOM,
            Constans::ROLE_DEPARTMENT,
            Constans::ROLE_SECTION,
            Constans::ROLE_MOH,
            Constans::ROLE_COLLEGE,
        ]);
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $stats = [];
        $role = $user->role;

        // 0. COLLEGE SUPERVISOR (ROLE_COLLEGE = 5)
        if ($role === Constans::ROLE_COLLEGE) {
            $college = $user->college;
            if ($college) {
                $totalTrainees = \App\Models\Trainees::where('college_id', $college->id)
                    ->whereHas('applications', fn($q) =>
                        $q->where('training_type', Constans::TRAINING_TYPE_UNIVERSITY)
                          ->whereIn('status', [Constans::STATUS_INITIAL_APPROVE, Constans::STATUS_STRATED_TRAINING, Constans::STATUS_ENDED_TRAINING])
                    )
                    ->count();
                $activeTrainees = Applications::whereHas('trainee', fn($q) => $q->where('college_id', $college->id))
                    ->where('status', Constans::STATUS_STRATED_TRAINING)
                    ->where('training_type', Constans::TRAINING_TYPE_UNIVERSITY)
                    ->count();

                $stats[] = Stat::make('إجمالي المتدربين (الكلية)', $totalTrainees)
                    ->icon('heroicon-o-academic-cap');

                $stats[] = Stat::make('قيد التدريب', $activeTrainees)
                    ->description('بدأوا التدريب فعلياً')
                    ->color('success')
                    ->icon('heroicon-o-user-group');
            }
        }

        // 0.5 MINISTRY OF HEALTH (ROLE_MOH = 4)
        elseif ($role === Constans::ROLE_MOH) {
            $totalApps = Applications::where('training_type', Constans::TRAINING_TYPE_PRACTICE)
                ->whereIn('status', [Constans::STATUS_INITIAL_APPROVE, Constans::STATUS_STRATED_TRAINING, Constans::STATUS_ENDED_TRAINING])
                ->count();
            $activeTrainees = Applications::where('training_type', Constans::TRAINING_TYPE_PRACTICE)
                ->where('status', Constans::STATUS_STRATED_TRAINING)
                ->count();

            $stats[] = Stat::make('إجمالي طلبات المزاولة', $totalApps)
                ->icon('heroicon-o-document-text');

            $stats[] = Stat::make('قيد التدريب', $activeTrainees)
                ->color('success')
                ->icon('heroicon-o-check-badge');
        }

        // 1. SECTION HEAD (ROLE_SECTION = 3)
        // Show Capacity for their section
        elseif ($role === Constans::ROLE_SECTION) {
            $section = Sections::where('user_id', $user->id)->first();
            if ($section) {
                $cap = $section->getCapacityStats();

                $stats[] = Stat::make('السعة الاستيعابية', $cap['total'])
                    ->description("القسم: {$section->name_location}")
                    ->icon('heroicon-o-users');

                $stats[] = Stat::make('المتدربين الحاليين', $cap['used'])
                    ->description("الأماكن المتاحة: " . $cap['available'])
                    ->color($cap['available'] <= 0 ? 'danger' : 'success')
                    ->icon('heroicon-o-user-group');
            }
        }

        // 2. DEPARTMENT HEAD (ROLE_DEPARTMENT = 2)
        // Show capacity for each section in their department + Sum
        elseif ($role === Constans::ROLE_DEPARTMENT) {
            $department = Departments::where('user_id', $user->id)->first();

            if ($department) {
                $cap = $department->getCapacityStats();

                $stats[] = Stat::make('إجمالي السعة (القسم)', $cap['total'])
                    ->description("الدائرة: {$department->title}")
                    ->icon('heroicon-o-building-office');

                $stats[] = Stat::make('إجمالي المتدربين', $cap['used'])
                     ->description("الشاغر كلياً: " . $cap['available'])
                     ->color('success');

            }
        }

        // 3. HEAD OF ADMINISTRATION (HOA) & HEAD OF MEDICAL (HOM)
        // HOA (6): Capacity for everything in his Admin (loop sections via Departments or direct if linked?)
        // Migration: Sections has 'administrative_id'.
        // HOM (7): Capacity for Health related stuff.
        elseif ($role === Constans::ROLE_HOA || $role === Constans::ROLE_HOM) {
            $cap = ['total' => 0, 'used' => 0, 'available' => 0];
            $adminUnitTitle = '';
            $sectionIds = [];

            if ($role === Constans::ROLE_HOA) {
                $adminUnit = \App\Models\Administrative::where('user_id', $user->id)->first();
                if ($adminUnit) {
                    $cap = $adminUnit->getCapacityStats();
                    $adminUnitTitle = "الوحدة الإدارية: {$adminUnit->title}";
                    $sectionIds = $adminUnit->sections->pluck('id');
                }
            } elseif ($role === Constans::ROLE_HOM) {
                $adminUnit = \App\Models\Administrative::where('medical_head_user_id', $user->id)->first();
                if ($adminUnit) {
                    // Logic to get ONLY medical section stats
                    $sections = Sections::where('administrative_id', $adminUnit->id)
                        ->whereHas('department', fn($q) => $q->where('is_medical', true))
                        ->get();

                    foreach($sections as $s) {
                        $sCap = $s->getCapacityStats();
                        $cap['total'] += $sCap['total'];
                        $cap['used'] += $sCap['used'];
                        $cap['available'] += $sCap['available'];
                    }
                    $adminUnitTitle = "الإدارة الطبية: {$adminUnit->title}";
                    $sectionIds = $sections->pluck('id');
                }
            }

            $pendingApps = Applications::whereIn('section_id', $sectionIds)
                ->where('status', Constans::STATUS_NEW)
                ->count();

            $stats[] = Stat::make('إجمالي السعة الاستيعابية', $cap['total'])
                ->description($adminUnitTitle)
                ->icon('heroicon-o-chart-bar');

            $stats[] = Stat::make('المتدربين النشطين', $cap['used'])
                ->description("الشاغر: " . $cap['available'])
                ->color('success');

            $stats[] = Stat::make('طلبات قيد الانتظار', $pendingApps)
                ->description('تحتاج مراجعة')
                ->color('warning');
        }

        // 4. GENERAL TRAINING MANAGER (GTM - 8)
        elseif ($role === Constans::ROLE_GTM) {

            $capStats = Constans::getCapacityStats(); // Get global stats
            $newApps = Applications::where('status', Constans::STATUS_NEW)->count();

            // Combined Capacity Card
            $stats[] = Stat::make('إجمالي السعة الاستيعابية', $capStats['total'])
                ->description("المستخدم: {$capStats['used']} | المتاح: {$capStats['available']}")
                ->icon('heroicon-o-chart-pie')
                ->color($capStats['available'] > 0 ? 'success' : 'danger');

            $stats[] = Stat::make('طلبات جديدة', $newApps)
                ->description('بانتظار الإجراء')
                ->color('warning');
        }

        // 5. ADMIN (ROLE_ADMIN - 1)
        elseif ($role === Constans::ROLE_ADMIN) {
            // "Last added users and stuff"
            // Stats: Total Users, Total Trainees, System Health?

            $stats[] = Stat::make('إجمالي المستخدمين', User::count())
                ->icon('heroicon-o-users')
                ->color('primary');

            $stats[] = Stat::make('إجمالي الطلبات', Applications::count())
                ->icon('heroicon-o-document-text')
                ->color('warning');

            $stats[] = Stat::make('الكليات المسجلة', \App\Models\College::count())
                ->icon('heroicon-o-academic-cap')
                ->color('success');
        }

        return $stats;
    }
}
