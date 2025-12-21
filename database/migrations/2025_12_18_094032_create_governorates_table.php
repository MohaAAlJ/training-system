<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // {'ar': 'خانيونس', 'en': 'Khan Yunis'}
            $table->timestamps();
        });
         DB::table('governorates')->insert([
            [
                'id' => 1,
                'name_en' => 'Gaza City',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name_en' => 'North Gaza',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name_en' => 'Deir Al-Balah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name_en' => 'Khan Yunis',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name_en' => 'Rafah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};
