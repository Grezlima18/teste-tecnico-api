<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateExamHash
{
    public function handle(Request $request, Closure $next): Response
    {
        $privateKey = config('exams.private_key');
        $receivedHash = $request->bearerToken();

        if (! is_string($privateKey) || $privateKey === '') {
            return new JsonResponse([
                'message' => 'Exam API private key is not configured.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $expectedHash = hash('sha256', $privateKey);

        if (! is_string($receivedHash) || ! hash_equals($expectedHash, $receivedHash)) {
            return new JsonResponse([
                'message' => 'Invalid exam API hash.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
