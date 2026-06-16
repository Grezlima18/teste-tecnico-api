<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ExamRequestController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\PatientsController;
use Illuminate\Support\Facades\Route;

Route::get('/patients', [PatientsController::class, 'index']);
Route::post('/patients', [PatientsController::class, 'store']);
Route::get('/patients/{id}', [PatientsController::class, 'show'])
    ->whereNumber('id');

Route::get('/exam-types', [ExamTypeController::class, 'index']);
Route::post('/exam-types', [ExamTypeController::class, 'store']);
Route::get('/exam-types/{id}', [ExamTypeController::class, 'show'])
    ->whereNumber('id');

Route::get('/attendances', [AttendanceController::class, 'index']);
Route::post('/attendances', [AttendanceController::class, 'store']);
Route::get('/attendances/{id}', [AttendanceController::class, 'show'])
    ->whereNumber('id');

Route::post('/exams', [ExamRequestController::class, 'store'])
    ->middleware('exam.hash');

Route::get('/exams/{protocol}', [ExamRequestController::class, 'show'])
    ->middleware('exam.hash');

Route::get('/exams/{protocol}/{examCode}', [ExamRequestController::class, 'show'])
    ->middleware('exam.hash');


