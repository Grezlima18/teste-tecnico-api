<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamTypeRequest;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ExamTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Exam::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Exam $exam): array => $this->formatExamType($exam))
                ->all(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $exam = Exam::query()->find($id);

        if ($exam === null) {
            return response()->json([
                'message' => 'Exam type not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $this->formatExamType($exam),
        ]);
    }

    public function store(StoreExamTypeRequest $request): JsonResponse
    {
        $exam = Exam::query()->create($request->validated());

        return response()->json([
            'message' => 'Exam type created.',
            'data' => $this->formatExamType($exam),
        ], Response::HTTP_CREATED);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatExamType(Exam $exam): array
    {
        return [
            'id' => $exam->id,
            'name' => $exam->name,
            'code' => $exam->code,
            'is_external' => $exam->is_external,
            'created_at' => $exam->created_at,
            'updated_at' => $exam->updated_at,
        ];
    }
}
