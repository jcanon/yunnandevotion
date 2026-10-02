<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('contributor');
            $table->boolean('active')->default(true);
            $table->string('locale', 10)->default('en');
        });
        Schema::create('interface_translations', function (Blueprint $table) {
            $table->id();
            $table->string('key', 160);
            $table->string('locale', 10);
            $table->text('value');
            $table->timestamps();
            $table->unique(['key', 'locale']);
        });
        Schema::create('sites', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('reference')->unique();
            $table->timestamps();
        });
        Schema::create('observations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->date('observed_from')->nullable();
            $table->date('observed_to')->nullable();
            $table->string('date_precision')->default('unknown');
            $table->string('condition')->default('unknown');
            $table->string('status')->default('draft')->index();
            $table->json('content');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_visibility')->default('approximate');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observations');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('interface_translations');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'active', 'locale']));
    }
};
