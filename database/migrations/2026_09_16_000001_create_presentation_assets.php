<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('key', 60)->primary();
            $table->string('name_en', 200);
            $table->string('name_zh', 200);
            $table->string('color', 7)->default('#24594c');
            $table->text('svg')->nullable();
            $table->timestamps();
        });
        foreach (['shrine' => ['Shrine', '小祠'], 'altar' => ['Altar', '供台'], 'incense' => ['Incense site', '香火点'], 'niche' => ['Niche', '壁龛'], 'other' => ['Other', '其他']] as $key => [$en,$zh]) {
            DB::table('categories')->insert(['key' => $key, 'name_en' => $en, 'name_zh' => $zh, 'color' => '#24594c', 'created_at' => now(), 'updated_at' => now()]);
        }
        Schema::create('homepage_images', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('alt_en', 500);
            $table->string('alt_zh', 500);
            $table->string('credit', 200);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_images');
        Schema::dropIfExists('categories');
    }
};
