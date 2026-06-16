<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_exams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('status', 30)->default('Pendente');
            $table->string('protocol')->nullable();
            $table->text('result')->nullable();
            $table->timestamps();

            $table->index(['attendance_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_exams');
    }
};
