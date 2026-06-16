<?php

use App\Http\Controllers\ExamRequestController;
use App\Http\Controllers\PatientsController;
use Illuminate\Support\Facades\Route;

Route::get('/patients', [PatientsController::class, 'index']);
Route::post('/patients', [PatientsController::class, 'store']);
Route::get('/patients/{id}', [PatientsController::class, 'show'])
    ->whereNumber('id');

Route::post('/exams', [ExamRequestController::class, 'store'])
    ->middleware('exam.hash');

Route::get('/exams/{protocol}', [ExamRequestController::class, 'show'])
    ->middleware('exam.hash');

Route::get('/exams/{protocol}/{examCode}', [ExamRequestController::class, 'show'])
    ->middleware('exam.hash');


