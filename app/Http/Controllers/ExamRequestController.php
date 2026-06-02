<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamRequest;
use App\Models\ExamRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ExamRequestController extends Controller
{
    public function store(StoreExamRequest $request): JsonResponse
    {
        if ($this->shouldFailRandomly()) {
            return response()->json([
                'message' => 'The exam request service is temporarily unavailable. Try again.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $payload = $request->validated();
        $protocol = $this->generateProtocol();
        $examRequests = DB::transaction(function () use ($payload, $protocol) {
            return collect($payload['exams'])
                ->map(function (array $exam) use ($payload, $protocol): ExamRequest {
                    $examCode = $exam['code'];

                    return ExamRequest::query()->create([
                        'external_service_id' => $payload['external_service_id'],
                        'protocol' => $protocol,
                        'requested_at' => $payload['requested_at'],
                        'patient_name' => $payload['patient']['name'],
                        'patient_document' => $payload['patient']['document'],
                        'patient_birth_date' => $payload['patient']['birth_date'] ?? null,
                        'exam_code' => $examCode,
                        'result' => config("exams.fake_results.{$examCode}"),
                        'status' => 'completed',
                        'requester_name' => $payload['requester']['name'],
                    ]);
                })
                ->values();
        });

        return response()->json([
            'message' => 'Exam request received.',
            'data' => [
                'external_service_id' => $payload['external_service_id'],
                'protocol' => $protocol,
                'status' => 'completed',
                'exams' => $examRequests->map(fn (ExamRequest $examRequest): array => [
                    'id' => $examRequest->id,
                    'exam_code' => $examRequest->exam_code,
                    'status' => $examRequest->status,
                    'created_at' => $examRequest->created_at,
                ])->all(),
            ],
        ], Response::HTTP_CREATED);
    }

    public function show(string $protocol, ?string $examCode = null): JsonResponse
    {
        $examRequests = ExamRequest::query()
            ->where('protocol', $protocol);

        if ($examCode !== null) {
            $examRequests->where('exam_code', strtoupper($examCode));
        }

        $examRequests = $examRequests->get();

        if ($examRequests->isEmpty()) {
            return response()->json([
                'message' => 'Exam result not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $firstExamRequest = $examRequests->first();

        return response()->json([
            'data' => [
                'external_service_id' => $firstExamRequest->external_service_id,
                'protocol' => $protocol,
                'patient_name' => $firstExamRequest->patient_name,
                'exams' => $examRequests->map(fn (ExamRequest $examRequest): array => [
                    'exam_code' => $examRequest->exam_code,
                    'result' => $examRequest->result,
                ])->all(),
            ],
        ]);
    }

    private function shouldFailRandomly(): bool
    {
        $failurePercent = max(0, min(100, (int) config('exams.random_failure_percent', 0)));

        return $failurePercent > 0 && random_int(1, 100) <= $failurePercent;
    }

    private function generateProtocol(): string
    {
        do {
            $protocol = 'PROTO-'.now()->format('Ymd').'-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (ExamRequest::query()->where('protocol', $protocol)->exists());

        return $protocol;
    }
}
