<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('exam_requests', 'external_id') || Schema::hasColumn('exam_requests', 'external_service_id')) {
            return;
        }

        Schema::table('exam_requests', function (Blueprint $table): void {
            $table->renameColumn('external_id', 'external_service_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('exam_requests', 'external_service_id') || Schema::hasColumn('exam_requests', 'external_id')) {
            return;
        }

        Schema::table('exam_requests', function (Blueprint $table): void {
            $table->renameColumn('external_service_id', 'external_id');
        });
    }
};
