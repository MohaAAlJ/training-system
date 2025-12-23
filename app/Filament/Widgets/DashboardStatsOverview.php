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
    protected int | string | array $columnSpan = 2;

    protected function getStats(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $stats = [];
        $role = $user->role;

        // 1. SECTION HEAD (ROLE_SECTION = 3)
        // Show Capacity for their section
        if ($role === Constans::ROLE_SECTION) {
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

                // Breakdown per section (limit to first 3 to avoid overcrowding, or just show main stats)
                // The widget area is grid. We can add more.
                // Let's add stats for up to 4 sections
                foreach($department->sections->take(4) as $sec) {
                    $secUsage = Applications::where('section_id', $sec->id)
                        ->where('status', Constans::STATUS_STRATED_TRAINING)
                        ->count();

                    $stats[] = Stat::make("سعة: {$sec->name_location}", $sec->total_capacity)
                        ->description("المشغول: {$secUsage}")
                        ->icon('heroicon-o-queue-list')
                        ->color('gray');
                }
            }
        }

        // 3. HEAD OF ADMINISTRATION (HOA) & HEAD OF MEDICAL (HOM)
        // HOA (6): Capacity for everything in his Admin (loop sections via Departments or direct if linked?)
        // Migration: Sections has 'administrative_id'.
        // HOM (7): Capacity for Health related stuff.
        elseif ($role === Constans::ROLE_HOA || $role === Constans::ROLE_HOM) {

            // For HOM, we filter by 'is_medical' = true in Departments or Administrative?
            // Usually HOM oversees Medical Departments across the board?
            // Or is it specific to an Administrative unit?
            // "administrative health show capacity ... administrative shows him capacity for everything"

            // Let's assume HOA sees ALL sections in their Administrative Unit.
            // Let's assume HOM sees ALL sections in Medical Departments (regardless of Admin? Or linked to their user?)
            // User model doesn't link HOM to a specific Admin directly in `users` table, but `administratives` table has `medical_head_user_id`.

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
