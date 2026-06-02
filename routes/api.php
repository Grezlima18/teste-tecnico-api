<?php

use App\Http\Controllers\ExamRequestController;
use Illuminate\Support\Facades\Route;

Route::post('/exams', [ExamRequestController::class, 'store'])
    ->middleware('exam.hash');

Route::get('/exams/{protocol}', [ExamRequestController::class, 'show'])
    ->middleware('exam.hash');

Route::get('/exams/{protocol}/{examCode}', [ExamRequestController::class, 'show'])
    ->middleware('exam.hash');
