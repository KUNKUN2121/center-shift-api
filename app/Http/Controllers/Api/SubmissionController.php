<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    /**
     * 自分の提出シフト一覧を取得
     * GET /api/submissions?shift_period_id=1
     */
    public function index(Request $request)
    {
        // バリデーション: 期間IDは必須
        $request->validate([
            'shift_period_id' => 'required|integer',
        ]);

        // ログイン中のユーザーの、指定された期間のシフトを取得
        $submissions = Submission::query()
            ->where('user_id', Auth::id())
            ->where('shift_period_id', $request->shift_period_id)
            ->orderBy('start_datetime') // 日付順に並べる
            ->get();

        return response()->json($submissions);
    }

    /**
     * シフト希望の一括保存（作成・更新・削除をまとめて行う）
     * POST /api/submissions/batch
     */
    public function updateBatch(Request $request)
    {
        // 1. バリデーション
        $validated = $request->validate([
            'shift_period_id' => 'required|integer|exists:shift_periods,id',
            'submissions'     => 'present|array', //全削除の場合 空配列で受け取る
            'submissions.*.start_datetime' => 'required|date',
            'submissions.*.end_datetime'   => 'required|date|after:submissions.*.start_datetime',
            'submissions.*.notes'          => 'nullable|string',
        ]);

        $periodId = $validated['shift_period_id'];
        $userId = Auth::id();
        $newSubmissionsData = $validated['submissions'];

        // 2. トランザクション（失敗したらロールバック）
        return DB::transaction(function () use ($userId, $periodId, $newSubmissionsData) {

            // A. まず、この期間の既存データを全て削除する
            Submission::where('user_id', $userId)
                ->where('shift_period_id', $periodId)
                ->delete();

            // B. 送られてきたデータを全て新規登録する
            foreach ($newSubmissionsData as $data) {
                Submission::create([
                    'user_id'         => $userId,
                    'shift_period_id' => $periodId,
                    'start_datetime'  => $data['start_datetime'],
                    'end_datetime'    => $data['end_datetime'],
                    'notes'           => $data['notes'] ?? null,
                    'status'          => 'draft', // 基本は下書きで保存
                ]);
            }

            // 最新の状態を返却
            return response()->json([
                'message' => 'Saved successfully',
                'data' => Submission::where('user_id', $userId)
                            ->where('shift_period_id', $periodId)
                            ->orderBy('start_datetime')
                            ->get()
            ]);
        });
    }
}
