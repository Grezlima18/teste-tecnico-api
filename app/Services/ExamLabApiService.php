<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceExam;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ExamLabApiService
{
    /**
     * @param  Collection<int, AttendanceExam>  $items
     */
    public function submit(Attendance $attendance, Collection $items): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $attendance->loadMissing('patient');
        $items->loadMissing('exam');

        $response = Http::withHeaders($this->authHeaders())
            ->post($this->endpoint('/api/exams'), [
                'external_service_id' => $attendance->id,
                'requested_at' => $attendance->requested_at->toIso8601String(),
                'patient' => [
                    'name' => $attendance->patient->name,
                    'sex' => $attendance->patient->sex,
                    'birth_date' => $attendance->patient->birth_date?->toDateString(),
                ],
                'exams' => $items->map(fn (AttendanceExam $item): array => [
                    'code' => $item->exam->code,
                ])->values()->all(),
                'requester' => [
                    'name' => 'Sistema',
                ],
            ]);

        if ($response->status() === 503) {
            $items->each(fn (AttendanceExam $item) => $item->update([
                'status' => AttendanceExam::STATUS_SENT,
            ]));

            return;
        }

        if (! $response->successful()) {
            return;
        }

        $protocol = $response->json('data.protocol');

        if (! is_string($protocol) || $protocol === '') {
            return;
        }

        $resultsByCode = $this->fetchResultsByCode($protocol);

        $items->each(function (AttendanceExam $item) use ($protocol, $resultsByCode): void {
            $code = $item->exam->code;

            $item->update([
                'protocol' => $protocol,
                'result' => $resultsByCode[$code] ?? config("exams.fake_results.{$code}"),
                'status' => AttendanceExam::STATUS_READY,
            ]);
        });
    }

    /**
     * @return array<string, string>
     */
    private function fetchResultsByCode(string $protocol): array
    {
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->endpoint("/api/exams/{$protocol}"));

        if (! $response->successful()) {
            return [];
        }

        $exams = $response->json('data.exams', []);

        if (! is_array($exams)) {
            return [];
        }

        $results = [];

        foreach ($exams as $exam) {
            if (! is_array($exam) || ! isset($exam['exam_code'], $exam['result'])) {
                continue;
            }

            $results[(string) $exam['exam_code']] = (string) $exam['result'];
        }

        return $results;
    }

    /**
     * @return array<string, string>
     */
    private function authHeaders(): array
    {
        $privateKey = config('exams.private_key');

        return [
            'Authorization' => 'Bearer '.hash('sha256', (string) $privateKey),
            'Accept' => 'application/json',
        ];
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('exams.api_url'), '/').$path;
    }
}
