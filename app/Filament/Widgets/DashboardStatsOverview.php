<?php

namespace App\Filament\Widgets;

use App\Models\Administrative;
use App\Models\Application;
use App\Models\Section;
use App\Models\Trainee;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $role = $user->role;

        return match ($role) {
            User::ROLE_COLLEGE => $this->getCollegeStats($user),
            User::ROLE_SECTION => $this->getSectionHeadStats($user),
            User::ROLE_DEPARTMENT => $this->getDepartmentHeadStats($user),
            User::ROLE_HOA, User::ROLE_HOM => $this->getHeadOfAdminOrMedicalStats($user),
            User::ROLE_MOH => $this->getMohStats($user),
            User::ROLE_GTM, User::ROLE_MONITOR => $this->getGtmStats($user),
            User::ROLE_ASSISTANT_TRAINING_MANAGER => $this->getGtmStats($user),
            User::ROLE_ADMIN => $this->getAdminStats(),
            default => [],
        };
    }

    private function getCollegeStats(User $user): array
    {
        $stats = [];
        $college = $user->college;
        if ($college) {
            if ($user->college()->active()->doesntExist()) {
                return [];
            }
            // Count trainees who have at least one application with this college
            $totalTrainees = Trainee::whereHas(
                'applications',
                fn($q) =>
                $q->where('college_id', $college->id)
                    ->where('training_type', Application::UNIVERSITY)
                    ->whereIn('status', [Application::STATUS_INITIAL_APPROVE, Application::STATUS_STARTED_TRAINING, Application::STATUS_ENDED_TRAINING])
            )
                ->count();

            $activeTrainees = Application::where('college_id', $college->id)
                ->where('status', Application::STATUS_STARTED_TRAINING)
                ->where('training_type', Application::UNIVERSITY)
                ->count();

            $stats[] = Stat::make('إجمالي المتدربين (الكلية)', $totalTrainees)
                ->icon('heroicon-o-academic-cap');

            $stats[] = Stat::make('قيد التدريب', $activeTrainees)
                ->description('بدأوا التدريب فعلياً')
                ->color('success')
                ->icon('heroicon-o-user-group');

            $pendingConfirmation = Application::where('college_id', $college->id)
                ->where('status', Application::STATUS_INITIAL_APPROVE)
                ->where('training_type', Application::UNIVERSITY)
                ->count();

            $stats[] = Stat::make('طلبات بانتظار التأكيد', $pendingConfirmation)
                ->description('بانتظار إجراء الكلية')
                ->color('warning')
                ->icon('heroicon-o-clock');
        }
        return $stats;
    }

    private function getSectionHeadStats(User $user): array
    {
        $stats = [];
        $section = $user->section()->active()->first();
        if ($section) {
            $cap = $section->getCapacityStats();

            $stats[] = Stat::make('السعة الاستيعابية', $cap['total'])
                ->description("القسم: {$section->name}")
                ->icon('heroicon-o-users');

            $stats[] = Stat::make('المتدربين الحاليين', $cap['used'])
                ->description("الأماكن المتاحة: " . $cap['available'])
                ->color($cap['available'] <= 0 ? 'danger' : 'success')
                ->icon('heroicon-o-user-group');
        }
        return $stats;
    }

    private function getDepartmentHeadStats(User $user): array
    {
        $stats = [];
        $department = $user->department()->active()->first();

        if ($department) {
            $cap = $department->getCapacityStats();

            $stats[] = Stat::make('إجمالي السعة (القسم)', $cap['total'])
                ->description("الدائرة: {$department->name}")
                ->icon('heroicon-o-building-office');

            $stats[] = Stat::make('إجمالي المتدربين', $cap['used'])
                ->description("الشاغر كلياً: " . $cap['available'])
                ->color('success');
        }
        return $stats;
    }

    private function getHeadOfAdminOrMedicalStats(User $user): array
    {
        $stats = [];
        $role = $user->role;
        $cap = ['total' => 0, 'used' => 0, 'available' => 0];
        $adminUnitTitle = '';
        $sectionIds = [];

        if ($role === User::ROLE_HOA) {
            $adminUnit = $user->administrative()->active()->first();
            if ($adminUnit) {
                $cap = $adminUnit->getCapacityStats();
                $adminUnitTitle = "الوحدة الإدارية: {$adminUnit->name}";
                $sectionIds = $adminUnit->sections()->active()->pluck('id');
            } else {
                return [];
            }
        } elseif ($role === User::ROLE_HOM) {
            $adminUnit = Administrative::where('medical_head_user_id', $user->id)->active()->first();
            if ($adminUnit) {
                // Logic to get ONLY medical section stats
                $sections = Section::where('administrative_id', $adminUnit->id)
                    ->active()
                    ->whereHas('departments', fn($q) => $q->where('is_medical', true)->active()->visible())
                    ->withCount(['applications as active_apps_count' => function ($q) {
                        $q->where('applications.status', Application::STATUS_STARTED_TRAINING);
                    }])
                    ->get();

                foreach ($sections as $s) {
                    $total = (int) ($s->capacity ?? 0);
                    $used = (int) $s->active_apps_count;
                    $available = max(0, $total - $used);

                    $cap['total'] += $total;
                    $cap['used'] += $used;
                    $cap['available'] += $available;
                }
                $adminUnitTitle = "الإدارة الطبية: {$adminUnit->name}";
                $sectionIds = $sections->pluck('id');
            } else {
                return [];
            }
        }

        $stats[] = Stat::make('إجمالي السعة الاستيعابية', $cap['total'])
            ->description($adminUnitTitle)
            ->icon('heroicon-o-chart-bar');

        $stats[] = Stat::make('المتدربين النشطين', $cap['used'])
            ->description("الشاغر: " . $cap['available'])
            ->color('success');

        return $stats;
    }

    private function getMohStats(User $user): array
    {
        $stats = [];
        if ($user->mohDepartment && $user->mohDepartment()->active()->doesntExist()) {
            return [];
        }

        $totalPracticeApps = Application::where('training_type', Application::PRACTICE)
            ->whereIn('status', [
                Application::STATUS_INITIAL_APPROVE,
                Application::STATUS_STARTED_TRAINING,
                Application::STATUS_ENDED_TRAINING
            ])
            ->when($user->mohDepartment, fn($q) => $q->whereHas('section', fn($s) => $s->whereHas('departments', fn($d) => $d->where('departments.id', $user->mohDepartment->id)->visible())))
            ->count();

        $activeTrainees = Application::where('training_type', Application::PRACTICE)
            ->where('status', Application::STATUS_STARTED_TRAINING)
            ->when($user->mohDepartment, fn($q) => $q->whereHas('section', fn($s) => $s->whereHas('departments', fn($d) => $d->where('departments.id', $user->mohDepartment->id)->visible())))
            ->count();

        $pendingConfirmation = Application::where('training_type', Application::PRACTICE)
            ->where('status', Application::STATUS_INITIAL_APPROVE)
            ->when($user->mohDepartment, fn($q) => $q->whereHas('section', fn($s) => $s->whereHas('departments', fn($d) => $d->where('departments.id', $user->mohDepartment->id)->visible())))
            ->count();

        $deptTitle = $user->mohDepartment ? "الدائرة: {$user->mohDepartment->name}" : 'جميع الدوائر الطبية';

        $stats[] = Stat::make('إجمالي طلبات الامتياز', $totalPracticeApps)
            ->description($deptTitle)
            ->icon('heroicon-o-document-text')
            ->color('primary');

        $stats[] = Stat::make('المتدربين النشطين', $activeTrainees)
            ->description('قيد التدريب حالياً')
            ->icon('heroicon-o-user-group')
            ->color('success');

        $stats[] = Stat::make('في انتظار التأكيد', $pendingConfirmation)
            ->description('يحتاج إلى مراجعة وقبول')
            ->icon('heroicon-o-clock')
            ->color('warning');

        return $stats;
    }

    private function getGtmStats(User $user): array
    {
        $capStats = $this->getSystemCapacityStats($user);

        $newApps = Application::query()->forUser($user)->whereIn('status', [
            Application::STATUS_NEW,
            Application::STATUS_CONFIRMATION,
            Application::STATUS_WAITING_LIST,
        ])->count();

        return [
            Stat::make('إجمالي السعة الاستيعابية', $capStats['total'])
                ->description("المستخدم: {$capStats['used']} | المتاح: {$capStats['available']}")
                ->icon('heroicon-o-chart-pie')
                ->color($capStats['available'] > 0 ? 'success' : 'danger'),

            Stat::make('طلبات جديدة', $newApps)
                ->description('بانتظار الإجراء')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            $user->isAssistantTrainingManager()
                ? Stat::make('الدوائر المُدارة', count($user->managedDepartmentIds()))
                    ->icon('heroicon-o-building-office-2')
                    ->color('primary')
                : Stat::make('إجمالي المستخدمين', User::active()->count())
                    ->icon('heroicon-o-users')
                    ->color('primary'),
        ];
    }

    private function getAdminStats(): array
    {
        $capStats = $this->getSystemCapacityStats();

        return [
            Stat::make('إجمالي السعة الاستيعابية', $capStats['total'])
                ->description("المستخدم: {$capStats['used']} | المتاح: {$capStats['available']}")
                ->icon('heroicon-o-chart-pie')
                ->color($capStats['available'] > 0 ? 'success' : 'danger'),

            Stat::make('إجمالي الطلبات', Application::count())
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('إجمالي المستخدمين', User::active()->count())
                ->icon('heroicon-o-users')
                ->color('primary'),
        ];
    }

    private function getSystemCapacityStats(?User $user = null): array
    {
        $capStats = ['total' => 0, 'used' => 0, 'available' => 0];

        $sections = Section::active()
            ->whereHas('administrative', fn($q) => $q->active())
            ->whereHas('departments', fn($q) => $q->active()->visible())
            ->when($user?->isAssistantTrainingManager(), fn($q) => $q->whereHas('departments', fn($d) => $d->whereIn('departments.id', $user->managedDepartmentIds())->visible()))
            ->withCount(['applications as active_apps_count' => fn($q) =>
                $q->where('applications.status', Application::STATUS_STARTED_TRAINING)
            ])
            ->get();

        foreach ($sections as $s) {
            $capStats['total'] += (int) ($s->capacity ?? 0);
            $capStats['used'] += (int) $s->active_apps_count;
        }
        $capStats['available'] = max(0, $capStats['total'] - $capStats['used']);

        return $capStats;
    }
}
