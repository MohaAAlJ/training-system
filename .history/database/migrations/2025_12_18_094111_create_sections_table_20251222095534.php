<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Departments;
use App\Models\Administrative;
use App\Models\Governorate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name_location'); 
            $table->foreignIdFor(Administrative::class)->constrained('administratives')->cascadeOnDelete();
            $table->foreignIdFor(Departments::class, 'department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignIdFor(Governorate::class)->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained('users')->nullOnDelete(); 
            $table->integer('total_capacity')->default(0);
            $table->integer('current_capacity')->default(0);
            $table->string('status')->default('active'); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
