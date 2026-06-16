<?php

namespace Database\Seeders;

use App\Models\Exam;
use Illuminate\Database\Seeder;

class ExamTypeSeeder extends Seeder
{
    public function run(): void
    {
        $examTypes = [
            [
                'name' => 'Hemograma',
                'code' => 'HEMO',
                'is_external' => true,
            ],
            [
                'name' => 'Testosterona Total',
                'code' => 'TESTO',
                'is_external' => true,
            ],
            [
                'name' => 'T4 Livre',
                'code' => 'T4L',
                'is_external' => true,
            ],
            [
                'name' => 'Glicose',
                'code' => 'GLIC',
                'is_external' => false,
            ],
        ];

        foreach ($examTypes as $examType) {
            Exam::query()->updateOrCreate(
                ['code' => $examType['code']],
                $examType,
            );
        }
    }
}
