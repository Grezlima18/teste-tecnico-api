<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_requests', function (Blueprint $table): void {
            $table->string('protocol')->nullable()->unique()->after('external_service_id');
            $table->text('result')->nullable()->after('exam_code');
        });
    }

    public function down(): void
    {
        Schema::table('exam_requests', function (Blueprint $table): void {
            $table->dropColumn(['protocol', 'result']);
        });
    }
};
