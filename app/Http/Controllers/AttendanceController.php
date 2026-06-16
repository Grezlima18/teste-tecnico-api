<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\AttendanceExam;
use App\Services\ExamLabApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly ExamLabApiService $examLabApiService,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Attendance::query()
                ->with(['patient', 'exams.exam'])
                ->orderByDesc('requested_at')
                ->get()
                ->map(fn (Attendance $attendance): array => $this->formatAttendance($attendance))
                ->all(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $attendance = Attendance::query()
            ->with(['patient', 'exams.exam'])
            ->find($id);

        if ($attendance === null) {
            return response()->json([
                'message' => 'Attendance not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $this->formatAttendance($attendance),
        ]);
    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $attendance = DB::transaction(function () use ($validated): Attendance {
            $attendance = Attendance::query()->create([
                'patient_id' => $validated['patient_id'],
                'requested_at' => now(),
            ]);

            foreach ($validated['exam_ids'] as $examId) {
                AttendanceExam::query()->create([
                    'attendance_id' => $attendance->id,
                    'exam_id' => $examId,
                    'status' => AttendanceExam::STATUS_PENDING,
                ]);
            }

            return $attendance;
        });

        $attendance->load(['patient', 'exams.exam']);

        $internalItems = $attendance->exams->filter(
            fn (AttendanceExam $item): bool => ! $item->exam->is_external
        );

        $internalItems->each(fn (AttendanceExam $item) => $item->update([
            'status' => AttendanceExam::STATUS_READY,
        ]));

        $externalItems = $attendance->exams->filter(
            fn (AttendanceExam $item): bool => $item->exam->is_external
        );

        if ($externalItems->isNotEmpty()) {
            $this->examLabApiService->submit($attendance, $externalItems);
        }

        $attendance->refresh()->load(['patient', 'exams.exam']);

        return response()->json([
            'message' => 'Attendance created.',
            'data' => $this->formatAttendance($attendance),
        ], Response::HTTP_CREATED);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAttendance(Attendance $attendance): array
    {
        return [
            'id' => $attendance->id,
            'patient_id' => $attendance->patient_id,
            'requested_at' => $attendance->requested_at,
            'patient' => $attendance->patient ? [
                'id' => $attendance->patient->id,
                'name' => $attendance->patient->name,
                'sex' => $attendance->patient->sex,
                'birth_date' => $attendance->patient->birth_date?->toDateString(),
            ] : null,
            'exams' => $attendance->exams
                ->map(fn (AttendanceExam $item): array => $this->formatAttendanceExam($item))
                ->all(),
            'created_at' => $attendance->created_at,
            'updated_at' => $attendance->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAttendanceExam(AttendanceExam $item): array
    {
        return [
            'id' => $item->id,
            'exam_id' => $item->exam_id,
            'name' => $item->exam?->name,
            'code' => $item->exam?->code,
            'is_external' => $item->exam?->is_external,
            'status' => $item->status,
            'protocol' => $item->protocol,
            'result' => $item->result,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    }
}
