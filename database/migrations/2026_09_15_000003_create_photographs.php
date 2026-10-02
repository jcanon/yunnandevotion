<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographs', function (Blueprint $t) {
            $t->ulid('id')->primary();
            $t->foreignUlid('observation_id')->constrained()->restrictOnDelete();
            $t->string('original_path');
            $t->string('web_path');
            $t->string('sha256', 64);
            $t->unsignedBigInteger('bytes');
            $t->text('caption_en')->nullable();
            $t->text('caption_zh')->nullable();
            $t->string('credit');
            $t->boolean('permission');
            $t->string('status')->default('pending');
            $t->text('scan_evidence')->nullable();
            $t->foreignId('reviewer_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photographs');
    }
};
