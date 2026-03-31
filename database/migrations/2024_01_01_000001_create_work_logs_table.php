<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('log_type', ['day_start', 'day_end']);
            $table->date('log_date');
            $table->json('section_a_items')->nullable();
            $table->json('section_b_items')->nullable();
            $table->longText('generated_text')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'log_type', 'log_date']);
            $table->index(['user_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs');
    }
};
