<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $now = Carbon::now();

        // Demo users that depend on seeded entities
        DB::table('users')->updateOrInsert(
            ['user_name' => 'department_head'],
            [
                'name' => 'Department Head',
                'email' => 'dept@example.com',
                'password' => Hash::make('123'),
                'role' => 2,
                'status' => 1,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['user_name' => 'section_head'],
            [
                'name' => 'Section Head',
                'email' => 'section@example.com',
                'password' => Hash::make('123'),
                'role' => 3,
                'status' => 1,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['user_name' => 'hoa_head'],
            [
                'name' => 'Head of Administrative',
                'email' => 'hoa@example.com',
                'password' => Hash::make('123'),
                'role' => 6,
                'status' => 1,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['user_name' => 'college_supervisor'],
            [
                'name' => 'College Supervisor',
                'email' => 'college@example.com',
                'password' => Hash::make('123'),
                'role' => 5,
                'status' => 1,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $deptHeadUserId = DB::table('users')->where('user_name', 'department_head')->value('id');
        $sectionHeadUserId = DB::table('users')->where('user_name', 'section_head')->value('id');
        $hoaUserId = DB::table('users')->where('user_name', 'hoa_head')->value('id');
        $collegeSupervisorUserId = DB::table('users')->where('user_name', 'college_supervisor')->value('id');

        if ($deptHeadUserId && Schema::hasTable('departments') && Schema::hasColumn('departments', 'user_id')) {
            DB::table('departments')->where('id', 1)->update(['user_id' => $deptHeadUserId]);
        }

        if ($sectionHeadUserId && Schema::hasTable('sections') && Schema::hasColumn('sections', 'user_id')) {
            DB::table('sections')->where('id', 1)->update(['user_id' => $sectionHeadUserId]);
        }

        if ($hoaUserId && Schema::hasTable('administratives') && Schema::hasColumn('administratives', 'user_id')) {
            DB::table('administratives')->where('id', 1)->update(['user_id' => $hoaUserId]);
        }

        if ($collegeSupervisorUserId && Schema::hasTable('colleges') && Schema::hasColumn('colleges', 'user_id')) {
            DB::table('colleges')->where('id', 1)->update(['user_id' => $collegeSupervisorUserId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $deptHeadUserId = DB::table('users')->where('user_name', 'department_head')->value('id');
        $sectionHeadUserId = DB::table('users')->where('user_name', 'section_head')->value('id');
        $hoaUserId = DB::table('users')->where('user_name', 'hoa_head')->value('id');
        $collegeSupervisorUserId = DB::table('users')->where('user_name', 'college_supervisor')->value('id');

        if ($deptHeadUserId && Schema::hasTable('departments') && Schema::hasColumn('departments', 'user_id')) {
            DB::table('departments')->where('id', 1)->where('user_id', $deptHeadUserId)->update(['user_id' => null]);
        }

        if ($sectionHeadUserId && Schema::hasTable('sections') && Schema::hasColumn('sections', 'user_id')) {
            DB::table('sections')->where('id', 1)->where('user_id', $sectionHeadUserId)->update(['user_id' => null]);
        }

        if ($hoaUserId && Schema::hasTable('administratives') && Schema::hasColumn('administratives', 'user_id')) {
            DB::table('administratives')->where('id', 1)->where('user_id', $hoaUserId)->update(['user_id' => null]);
        }

        if ($collegeSupervisorUserId && Schema::hasTable('colleges') && Schema::hasColumn('colleges', 'user_id')) {
            DB::table('colleges')->where('id', 1)->where('user_id', $collegeSupervisorUserId)->update(['user_id' => null]);
        }

        // Remove demo users (keep superadmin + training manager in users migration)
        DB::table('users')->whereIn('user_name', [
            'department_head',
            'section_head',
            'hoa_head',
            'college_supervisor',
        ])->delete();
    }
};
