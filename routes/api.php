<?php

use App\Http\Controllers\Api\SubmissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {

    // 一覧取得
    Route::get('/submissions', [SubmissionController::class, 'index']);

    // 一括保存
    Route::post('/submissions/batch', [SubmissionController::class, 'updateBatch']);

});
