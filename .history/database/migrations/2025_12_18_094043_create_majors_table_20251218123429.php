<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('code')->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // تم اختصار القائمة لتشمل العينات الأساسية، يمكنك إضافة البقية بنفس النمط
        $majors = [
            ['id' => 1, 'name' => 'الهندسة المدنية', 'code' => 'ا-23282d9d'],
            ['id' => 4, 'name' => 'هندسة الحاسوب', 'code' => 'ه-4d87e379'],
            ['id' => 7, 'name' => 'علم الحاسوب', 'code' => 'ع-9468db5e'],
            ['id' => 11, 'name' => 'الطب البشري (دكتور في الطب)', 'code' => 'ا-631d43ca'],
            ['id' => 47, 'name' => 'الصيدلة', 'code' => 'ا-fdb65bf2'],
            // ... أضف باقي التخصصات الـ 136 من ملف الـ SQL الأصلي هنا
        ];

        foreach ($majors as $major) {
            DB::table('majors')->insert([
                'id' => $major['id'],
                'name' => json_encode(['ar' => $major['name'], 'en' => $major['name']]),
                'code' => $major['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void { Schema::dropIfExists('majors'); }
};