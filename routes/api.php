<?php

use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\SubmissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {

    // 一覧取得
    Route::get('/submissions', [SubmissionController::class, 'index']);

    // submission
    // 1件作成
    Route::post('/submissions', [SubmissionController::class, 'store']);
    // 1件更新
    Route::put('/submissions/{id}', [SubmissionController::class, 'update']);
    // 削除
    Route::delete('/submissions/{id}', [SubmissionController::class, 'destroy']);

    // 確定シフト一覧取得
    Route::get('/shifts', [ShiftController::class, 'index']);

});
