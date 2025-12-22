<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Governorate;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // عمود من نوع JSON
            $table->timestamps();
        });

        $governorates = [
            ['name' => ['ar' => 'غزة', 'en' => 'Gaza']],
            ['name' => ['ar' => 'شمال غزة', 'en' => 'North Gaza']],
            ['name' => ['ar' => 'خانيونس', 'en' => 'Khan Yunis']],
            ['name' => ['ar' => 'رفح', 'en' => 'Rafah']],
            ['name' => ['ar' => 'دير البلح', 'en' => 'Deir al-Balah']],
        ];

        foreach ($governorates as $gov) {
            Governorate::create($gov);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};