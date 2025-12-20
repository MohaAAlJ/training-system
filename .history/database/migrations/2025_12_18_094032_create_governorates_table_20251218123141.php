<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->json('name'); 
            $table->timestamps();
        });

        // 2. إضافة البيانات (Seeding)
        $governorates = [
            ['ar' => 'غزة', 'en' => 'Gaza'],
            ['ar' => 'شمال غزة', 'en' => 'North Gaza'],
            ['ar' => 'خانيونس', 'en' => 'Khan Yunis'],
            ['ar' => 'رفح', 'en' => 'Rafah'],
            ['ar' => 'دير البلح', 'en' => 'Deir al-Balah'],
        ];

        foreach ($governorates as $gov) {
            DB::table('governorates')->insert([
                'name' => json_encode($gov),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};
