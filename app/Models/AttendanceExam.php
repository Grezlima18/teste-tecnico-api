<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceExam extends Model
{
    public const STATUS_PENDING = 'Pendente';

    public const STATUS_SENT = 'Enviado ao Apoio';

    public const STATUS_READY = 'Exame Pronto';

    protected $fillable = [
        'attendance_id',
        'exam_id',
        'status',
        'protocol',
        'result',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
