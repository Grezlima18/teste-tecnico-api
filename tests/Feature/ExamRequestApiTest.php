<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamRequestApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'exams.private_key' => 'test-private-key',
            'exams.random_failure_percent' => 0,
        ]);
    }

    public function test_it_stores_multiple_exam_requests_with_one_shared_protocol(): void
    {
        $response = $this->postExamJson($this->validPayload(), 'test-private-key');

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Exam request received.')
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.exams.0.status', 'completed')
            ->assertJsonPath('data.exams.0.exam_code', 'TESTO')
            ->assertJsonPath('data.exams.1.status', 'completed')
            ->assertJsonPath('data.exams.1.exam_code', 'HEMO')
            ->assertJsonCount(2, 'data.exams')
            ->assertJsonStructure([
                'data' => [
                    'external_service_id',
                    'protocol',
                    'status',
                    'exams' => [
                        '*' => [
                            'id',
                            'exam_code',
                            'status',
                            'created_at',
                        ],
                    ],
                ],
            ]);

        $protocol = $response->json('data.protocol');

        $this->assertDatabaseHas('exam_requests', [
            'external_service_id' => 1001,
            'protocol' => $protocol,
            'exam_code' => 'TESTO',
            'status' => 'completed',
            'result' => 'Testosterona total: 560 ng/dL',
        ]);

        $this->assertDatabaseHas('exam_requests', [
            'external_service_id' => 1001,
            'protocol' => $protocol,
            'exam_code' => 'HEMO',
            'status' => 'completed',
            'result' => 'Hemograma completo: sem alteracoes relevantes',
        ]);
    }

    public function test_it_returns_all_exam_results_by_protocol(): void
    {
        $protocol = $this
            ->postExamJson($this->validPayload(), 'test-private-key')
            ->json('data.protocol');

        $response = $this->getJsonWithHash("/api/exams/{$protocol}", 'test-private-key');

        $response
            ->assertOk()
            ->assertJsonPath('data.external_service_id', 1001)
            ->assertJsonPath('data.protocol', $protocol)
            ->assertJsonPath('data.patient_name', 'Jane Doe')
            ->assertJsonPath('data.exams.0.exam_code', 'TESTO')
            ->assertJsonPath('data.exams.0.result', 'Testosterona total: 560 ng/dL')
            ->assertJsonPath('data.exams.1.exam_code', 'HEMO')
            ->assertJsonPath('data.exams.1.result', 'Hemograma completo: sem alteracoes relevantes');
    }

    public function test_it_returns_a_specific_exam_result_by_protocol_and_exam_code(): void
    {
        $protocol = $this
            ->postExamJson($this->validPayload(), 'test-private-key')
            ->json('data.protocol');

        $response = $this->getJsonWithHash("/api/exams/{$protocol}/HEMO", 'test-private-key');

        $response
            ->assertOk()
            ->assertJsonPath('data.external_service_id', 1001)
            ->assertJsonPath('data.protocol', $protocol)
            ->assertJsonPath('data.patient_name', 'Jane Doe')
            ->assertJsonCount(1, 'data.exams')
            ->assertJsonPath('data.exams.0.exam_code', 'HEMO')
            ->assertJsonPath('data.exams.0.result', 'Hemograma completo: sem alteracoes relevantes');
    }

    public function test_it_rejects_requests_without_a_valid_hash(): void
    {
        $response = $this->postExamJson($this->validPayload(), 'test-private-key', 'wrong-hash');

        $response->assertUnauthorized();
        $this->assertDatabaseCount('exam_requests', 0);
    }

    public function test_it_validates_the_payload(): void
    {
        $response = $this->postExamJson([], 'test-private-key');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'external_service_id',
                'requested_at',
                'patient',
                'exams',
                'requester',
            ]);
    }

    public function test_it_rejects_unsupported_exam_codes(): void
    {
        $payload = $this->validPayload();
        $payload['exams'][0]['code'] = 'CBC';

        $response = $this->postExamJson($payload, 'test-private-key');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['exams.0.code']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'external_service_id' => 1001,
            'requested_at' => '2026-05-29T10:30:00-03:00',
            'patient' => [
                'name' => 'Jane Doe',
                'sex' => 'f',
                'birth_date' => '1990-03-10',
            ],
            'exams' => [
                [
                    'code' => 'TESTO',
                ],
                [
                    'code' => 'HEMO',
                ],
            ],
            'requester' => [
                'name' => 'Dr. House',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postExamJson(array $payload, string $privateKey, ?string $hash = null): \Illuminate\Testing\TestResponse
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $hash ??= hash('sha256', $privateKey);

        return $this->call('POST', '/api/exams', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$hash}",
        ], $body);
    }

    private function getJsonWithHash(string $uri, string $privateKey, ?string $hash = null): \Illuminate\Testing\TestResponse
    {
        $hash ??= hash('sha256', $privateKey);

        return $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$hash}",
        ])->getJson($uri);
    }
}
