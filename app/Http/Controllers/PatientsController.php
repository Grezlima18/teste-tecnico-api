<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Models\Patients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PatientsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Patients::query()->orderBy('name');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->string('name').'%');
        }

        return response()->json([
            'data' => $query->get()->map(fn (Patients $patient): array => $this->formatPatient($patient))->all(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $patient = Patients::query()->find($id);

        if ($patient === null) {
            return response()->json([
                'message' => 'Patient not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $this->formatPatient($patient),
        ]);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = Patients::query()->create($request->validated());

        return response()->json([
            'message' => 'Patient created.',
            'data' => $this->formatPatient($patient),
        ], Response::HTTP_CREATED);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPatient(Patients $patient): array
    {
        return [
            'id' => $patient->id,
            'name' => $patient->name,
            'sex' => $patient->sex,
            'birth_date' => $patient->birth_date?->toDateString(),
            'created_at' => $patient->created_at,
            'updated_at' => $patient->updated_at,
        ];
    }
}
