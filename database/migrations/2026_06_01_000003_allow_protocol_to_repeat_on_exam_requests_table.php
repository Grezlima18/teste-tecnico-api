<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_requests', function (Blueprint $table): void {
            $table->dropUnique('exam_requests_protocol_unique');
            $table->index('protocol');
        });
    }

    public function down(): void
    {
        Schema::table('exam_requests', function (Blueprint $table): void {
            $table->dropIndex('exam_requests_protocol_index');
            $table->unique('protocol');
        });
    }
};
