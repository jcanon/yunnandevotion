<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->foreignUlid('revises_id')->nullable()->constrained('observations')->restrictOnDelete();
        });
        Schema::create('observation_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('observation_id')->constrained()->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->string('decision');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_decisions');
        Schema::table('observations', fn (Blueprint $table) => $table->dropConstrainedForeignId('revises_id'));
    }
};
