<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamRequest extends Model
{
    protected $fillable = [
        'external_service_id',
        'protocol',
        'requested_at',
        'patient_name',
        'patient_birth_date',
        'exam_code',
        'result',
        'exam_name',
        'priority',
        'requester_name',
        'requester_email',
        'notes',
        'status',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'patient_birth_date' => 'date',
            'payload' => 'array',
        ];
    }
}
