<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_name')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->tinyInteger('role')->default(null)->nullable(); // 1:Admin,, 2:Dept Manager, 3:Section Head, 4:ministry of health, 5:College Supervisor, 6: head of adminstrative, 7:head of medical 8: training manager
            $table->boolean('status')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignIdFor(User::class)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        User::create([
            'user_name' => 'admin',
            'name' => 'moha Admin',
            'email' => 'Moha@admins.com',
            'password' => Hash::make('123'),
            'role' => User::ROLE_ADMIN,
            'status' => 1,
        ]);

        // Department Head - role 2
        User::create([
            'user_name' => 'department_head',
            'name' => 'Department Head',
            'email' => 'dept@example.com',
            'password' => Hash::make('123'),
            'role' => User::ROLE_DEPARTMENT,
            'status' => 1,
        ]);

        // Section Head - role 3
        User::create([
            'user_name' => 'section_head',
            'name' => 'Section Head',
            'email' => 'section@example.com',
            'password' => Hash::make('123'),
            'role' => User::ROLE_SECTION,
            'status' => 1,
        ]);

        // Head of Administrative (HOA) - role 4
        User::create([
            'user_name' => 'hoa_head',
            'name' => 'Head of Administrative',
            'email' => 'hoa@example.com',
            'password' => Hash::make('123'),
            'role' => User::ROLE_HOA,
            'status' => 1,
        ]);

        // College Supervisor - role 5
        User::create([
            'user_name' => 'college_supervisor',
            'name' => 'College Supervisor',
            'email' => 'college@example.com',
            'password' => Hash::make('123'),
            'role' => User::ROLE_COLLEGE,
            'status' => 1,
        ]);

        // General Training Manager - role 8
        User::create([
            'user_name' => 'training_manager',
            'name' => 'General Training Manager',
            'email' => 'gtm@example.com',
            'password' => Hash::make('123'),
            'role' => User::ROLE_GTM,
            'status' => 1,
        ]);

        // Set up relationships for test users
        // Department Head - connect to Department with id=1
        $deptHeadUser = User::where('user_name', 'department_head')->first();
        if ($deptHeadUser) {
            DB::table('departments')->where('id', 1)->update(['user_id' => $deptHeadUser->id]);
        }

        // Section Head - connect to Section with id=1
        $sectionHeadUser = User::where('user_name', 'section_head')->first();
        if ($sectionHeadUser) {
            DB::table('sections')->where('id', 1)->update(['user_id' => $sectionHeadUser->id]);
        }

        // Head of Administrative - connect to Administrative with id=1
        $hoaUser = User::where('user_name', 'hoa_head')->first();
        if ($hoaUser) {
            DB::table('administratives')->where('id', 1)->update(['user_id' => $hoaUser->id]);
        }

        // College Supervisor - connect to College with id=1
        $collegeSupervisorUser = User::where('user_name', 'college_supervisor')->first();
        if ($collegeSupervisorUser) {
            DB::table('colleges')->where('id', 1)->update(['user_id' => $collegeSupervisorUser->id]);
        }
    }


    /**
     *  Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
