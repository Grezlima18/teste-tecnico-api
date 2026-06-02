<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_requests', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('external_service_id')->index();
            $table->timestamp('requested_at');
            $table->string('patient_name');
            $table->string('patient_document', 50);
            $table->date('patient_birth_date')->nullable();
            $table->string('exam_code', 80);
            $table->string('requester_name');
            $table->string('requester_email')->nullable();
            $table->string('status', 30)->default('received');
            $table->timestamps();

            $table->index(['patient_document', 'exam_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_requests');
    }
};
