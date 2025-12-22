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
            $table->json('name');
            $table->timestamps();
        });

        $governorates = [
            ['name' => 'غزة'],
            ['name' => 'شمال غزة'],
            ['name' => 'خانيونس'],
            ['name' => 'رفح'],
            ['name' => 'دير البلح'],
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
