<?php

namespace App\Helpers;

use App\Models\Section;
use App\Models\Department;
use App\Models\Administrative;
use App\Models\Application;

class Constants
{
    /**
     * Helper to calculate capacity and usage
     */
    public static function getCapacityStats(?int $administrativeId = null, ?int $departmentId = null, ?int $sectionId = null, bool $isMedical = false): array
    {
        if ($sectionId) {
            $section = Section::find($sectionId);
            return $section ? $section->getCapacityStats() : ['total' => 0, 'used' => 0, 'available' => 0];
        }

        if ($departmentId) {
            $dept = Department::find($departmentId);
            return $dept ? $dept->getCapacityStats() : ['total' => 0, 'used' => 0, 'available' => 0];
        }

        if ($administrativeId) {
            $admin = Administrative::find($administrativeId);
            return $admin ? $admin->getCapacityStats() : ['total' => 0, 'used' => 0, 'available' => 0];
        }

        // Global stats (if no specific ID)
        $Section = Section::query();
        if ($isMedical) {
            $Section->whereHas('department', fn($q) => $q->where('is_medical', true));
        }

        $stats = ['total' => 0, 'used' => 0, 'available' => 0];
        foreach ($Section->get() as $section) {
            $sStats = $section->getCapacityStats();
            $stats['total'] += $sStats['total'];
            $stats['used'] += $sStats['used'];
            $stats['available'] += $sStats['available'];
        }

        return $stats;
    }
}





