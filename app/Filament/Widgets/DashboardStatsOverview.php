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
        $user = Auth::user();
        if ($user && in_array($user->role, [Constans::ROLE_MOH, Constans::ROLE_COLLEGE])) {
            return 1; // It will take 1 column in a multi-column grid
        }
        return 'full';
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
                // Determine current usage (count active applications in this section)
                // Assuming 'active' implies being in training (STATUS_STRATED_TRAINING = 5)
                // Or maybe just total accepted? Using STATUS_STRATED_TRAINING for now.
                $currentTrainees = Applications::where('section_id', $section->id)
                    ->whereIn('status', [Constans::STATUS_STRATED_TRAINING])
                    ->count();

                $stats[] = Stat::make('السعة الاستيعابية', $section->total_capacity)
                    ->description("القسم: {$section->name_location}")
                    ->icon('heroicon-o-users');

                $stats[] = Stat::make('المتدربين الحاليين', $currentTrainees)
                    ->description("الأماكن المتاحة: " . max(0, $section->total_capacity - $currentTrainees))
                    ->color($currentTrainees >= $section->total_capacity ? 'danger' : 'success')
                    ->icon('heroicon-o-user-group');
            }
        }

        // 2. DEPARTMENT HEAD (ROLE_DEPARTMENT = 2)
        // Show capacity for each section in their department + Sum
        elseif ($role === Constans::ROLE_DEPARTMENT) {
            $department = Departments::where('user_id', $user->id)->with('sections')->first();

            if ($department) {
                $totalCapacity = $department->sections->sum('total_capacity');

                // Get active trainees in this department
                $currentTrainees = Applications::where('department_id', $department->id)
                    ->where('status', Constans::STATUS_STRATED_TRAINING)
                    ->count();

                $stats[] = Stat::make('إجمالي السعة (القسم)', $totalCapacity)
                    ->description("الدائرة: {$department->title}")
                    ->icon('heroicon-o-building-office');

                $stats[] = Stat::make('إجمالي المتدربين', $currentTrainees)
                     ->description("الشاغر كلياً: " . ($totalCapacity - $currentTrainees))
                     ->color('success');

            }
        }

        // 3. HEAD OF ADMINISTRATION (HOA) & HEAD OF MEDICAL (HOM)
        // HOA (6): Capacity for everything in his Admin (loop sections via Departments or direct if linked?)
        // Migration: Sections has 'administrative_id'.
        // HOM (7): Capacity for Health related stuff.
        elseif ($role === Constans::ROLE_HOA || $role === Constans::ROLE_HOM) {



            $sectionsQuery = Sections::query();

            if ($role === Constans::ROLE_HOA) {
                // Find the Administrative where this user is the Manager
                $adminUnit = \App\Models\Administrative::where('user_id', $user->id)->first();
                if ($adminUnit) {
                    $sectionsQuery->where('administrative_id', $adminUnit->id);
                    $stats[] = Stat::make('الوحدة الإدارية', $adminUnit->title)->color('primary');
                }
            } elseif ($role === Constans::ROLE_HOM) {
                // Find Administrative where this user is Medical Head
                $adminUnit = \App\Models\Administrative::where('medical_head_user_id', $user->id)->first();
                 if ($adminUnit) {
                    // Filter for Medical Departments only within this Admin?
                    // "show capacity for health and applications for health related stuff"
                    // Depts have `is_medical`.
                    $sectionsQuery->where('administrative_id', $adminUnit->id)
                                  ->whereHas('department', fn($q) => $q->where('is_medical', true));

                    $stats[] = Stat::make('الإدارة الطبية', $adminUnit->title)->color('danger');
                }
            }

            // Calculate Aggregates
            $sections = $sectionsQuery->get();
            $totalCapacity = $sections->sum('total_capacity');
            $sectionIds = $sections->pluck('id');

            $currentTrainees = Applications::whereIn('section_id', $sectionIds)
                ->where('status', Constans::STATUS_STRATED_TRAINING)
                ->count();

            $pendingApps = Applications::whereIn('section_id', $sectionIds)
                ->where('status', Constans::STATUS_NEW)
                ->count();

            $stats[] = Stat::make('إجمالي السعة الاستيعابية', $totalCapacity)
                ->icon('heroicon-o-chart-bar');

            $stats[] = Stat::make('المتدربين النشطين', $currentTrainees)
                ->description("الشاغر: " . ($totalCapacity - $currentTrainees))
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
