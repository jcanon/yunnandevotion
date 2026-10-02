<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $t) {
            $t->ulid('id')->primary();
            $t->foreignUlid('observation_id')->constrained()->restrictOnDelete();
            $t->string('original_path');
            $t->string('original_sha256', 64);
            $t->string('web_path')->nullable();
            $t->string('web_sha256', 64)->nullable();
            $t->string('web_mime')->nullable();
            $t->text('title_en')->nullable();
            $t->text('title_zh')->nullable();
            $t->string('credit');
            $t->boolean('permission');
            $t->longText('transcript_en')->nullable();
            $t->longText('transcript_zh')->nullable();
            $t->longText('captions_en')->nullable();
            $t->longText('captions_zh')->nullable();
            $t->unsignedInteger('duration_seconds')->nullable();
            $t->string('status')->default('pending');
            $t->text('scan_evidence')->nullable();
            $t->foreignId('reviewer_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
