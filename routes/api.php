<?php

use App\Http\Controllers\Api\Admin\ShiftPeriodController;
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

    //periods
    // user用 募集中の期間一覧取得
    Route::get('/periods/open', [ShiftPeriodController::class, 'periodOpen']);

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

// 管理者用
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // 期間一覧
    Route::get('/periods', [ShiftPeriodController::class, 'index']);
    // 新規期間作成
    Route::post('/periods', [ShiftPeriodController::class, 'store']);
    // ステータス変更
    Route::patch('/periods/{id}/status', [ShiftPeriodController::class, 'updateStatus']);
});
