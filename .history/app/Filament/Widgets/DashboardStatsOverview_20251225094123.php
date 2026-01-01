<?php

namespace App\Filament\Widgets;

use App\Helpers\Constants;
use App\Models\Action;
use App\Models\Application;
use App\Models\Department;
use App\Models\Section;
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
            User::ROLE_GTM,
            User::ROLE_ADMIN,
            User::ROLE_HOA,
            User::ROLE_HOM,
            User::ROLE_DEPARTMENT,
            User::ROLE_SECTION,
            User::ROLE_MOH,
            User::ROLE_COLLEGE,
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
        if ($role === User::ROLE_COLLEGE) {
            $college = $user->college;
            if ($college) {
                $totalTrainees = \App\Models\Trainee::where('college_id', $college->id)
                    ->whereHas(
                        'Application',
                        fn($q) =>
                        $q->where('training_type', Application::TRAINING_TYPE_UNIVERSITY)
                            ->whereIn('status', [Application::STATUS_INITIAL_APPROVE, Application::STATUS_STARTED_TRAINING, Application::STATUS_ENDED_TRAINING])
                    )
                    ->count();
                $activeTrainees = Application::whereHas('trainee', fn($q) => $q->where('college_id', $college->id))
                    ->where('status', Application::STATUS_STARTED_TRAINING)
                    ->where('training_type', Application::TRAINING_TYPE_UNIVERSITY)
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
        elseif ($role === User::ROLE_MOH) {
            $totalApps = Application::where('training_type', Application::TRAINING_TYPE_PRACTICE)
                ->whereIn('status', [Application::STATUS_INITIAL_APPROVE, Application::STATUS_STARTED_TRAINING, Application::STATUS_ENDED_TRAINING])
                ->count();
            $activeTrainees = Application::where('training_type', Application::TRAINING_TYPE_PRACTICE)
                ->where('status', Application::STATUS_STARTED_TRAINING)
                ->count();

            $stats[] = Stat::make('إجمالي طلبات المزاولة', $totalApps)
                ->icon('heroicon-o-document-text');

            $stats[] = Stat::make('قيد التدريب', $activeTrainees)
                ->color('success')
                ->icon('heroicon-o-check-badge');
        }

        // 1. SECTION HEAD (ROLE_SECTION = 3)
        // Show Capacity for their section
        elseif ($role === User::ROLE_SECTION) {
            $section = Section::where('user_id', $user->id)->first();
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
        elseif ($role === User::ROLE_DEPARTMENT) {
            $department = Department::where('user_id', $user->id)->first();

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
        // HOA (6): Capacity for everything in his Admin (loop Section via Department or direct if linked?)
        // Migration: Section has 'administrative_id'.
        // HOM (7): Capacity for Health related stuff.
        elseif ($role === User::ROLE_HOA || $role === User::ROLE_HOM) {
            $cap = ['total' => 0, 'used' => 0, 'available' => 0];
            $adminUnitTitle = '';
            $sectionIds = [];

            if ($role === User::ROLE_HOA) {
                $adminUnit = \App\Models\Administrative::where('user_id', $user->id)->first();
                if ($adminUnit) {
                    $cap = $adminUnit->getCapacityStats();
                    $adminUnitTitle = "الوحدة الإدارية: {$adminUnit->title}";
                    $sectionIds = $adminUnit->Section->pluck('id');
                }
            } elseif ($role === User::ROLE_HOM) {
                $adminUnit = \App\Models\Administrative::where('medical_head_user_id', $user->id)->first();
                if ($adminUnit) {
                    // Logic to get ONLY medical section stats
                    $Section = Section::where('administrative_id', $adminUnit->id)
                        ->whereHas('department', fn($q) => $q->where('is_medical', true))
                        ->get();

                    foreach ($Section as $s) {
                        $sCap = $s->getCapacityStats();
                        $cap['total'] += $sCap['total'];
                        $cap['used'] += $sCap['used'];
                        $cap['available'] += $sCap['available'];
                    }
                    $adminUnitTitle = "الإدارة الطبية: {$adminUnit->title}";
                    $sectionIds = $Section->pluck('id');
                }
            }

            $pendingApps = Application::whereIn('section_id', $sectionIds)
                ->where('status', Application::STATUS_NEW)
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
        elseif ($role === User::ROLE_GTM) {

            $capStats = Constants::getCapacityStats(); // Get global stats
            $newApps = Application::where('status', Application::STATUS_NEW)->count();

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
        elseif ($role === User::ROLE_ADMIN) {
            // "Last added users and stuff"
            // Stats: Total Users, Total Trainee, System Health?

            $stats[] = Stat::make('إجمالي المستخدمين', User::count())
                ->icon('heroicon-o-users')
                ->color('primary');

            $stats[] = Stat::make('إجمالي الطلبات', Application::count())
                ->icon('heroicon-o-document-text')
                ->color('warning');

            $stats[] = Stat::make('الكليات المسجلة', \App\Models\College::count())
                ->icon('heroicon-o-academic-cap')
                ->color('success');
        }

        return $stats;
    }
}
