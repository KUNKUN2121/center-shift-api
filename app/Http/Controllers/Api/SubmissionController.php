<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
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

        // periodがopenじゃない場合は弾く
        $periodStatus = DB::table('shift_periods')
            ->where('id', $request->shift_period_id)
            ->value('status');
        if ($periodStatus !== 'open') {
            return response()->json(['message' => '指定されたシフト期間は提出できません。'], 403);
        }

        // ログイン中のユーザーの、指定された期間のシフトを取得
        $submissions = Submission::query()
            ->where('user_id', Auth::id())
            ->where('shift_period_id', $request->shift_period_id)
            ->orderBy('start_datetime') // 日付順に並べる
            ->get();



        return response()->json($submissions);
    }

    /**
     * 1件作成
     * POST /api/submissions
     */
    public function store(StoreSubmissionRequest $request)
    {
        // バリデーション済みのデータのみ取得
        $validated = $request->validated();

        $submission = Submission::create([
            'user_id'         => Auth::id(),
            'shift_period_id' => $validated['shift_period_id'],
            'start_datetime'  => $validated['start_datetime'],
            'end_datetime'    => $validated['end_datetime'],
            'notes'           => $validated['notes'] ?? null,
            'status'          => 'draft',
        ]);

        return response()->json($submission, 201);
    }

    /**
     * 1件更新
     * PUT /api/submissions/{id}
     * ここも StoreSubmissionRequest を使います（IDチェックのロジックを入れたので流用可能）
     */
    public function update(StoreSubmissionRequest $request, string $id)
    {
        // 自分のデータか確認
        $submission = Submission::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validated();

        $submission->update($validated);

        return response()->json($submission);
    }

    /**
     * 1件削除
     * DELETE /api/submissions/{id}
     */
    public function destroy(string $id)
    {
        $submission = Submission::where('user_id', Auth::id())->findOrFail($id);
        $submission->delete();

        return response()->json(null, 204); // No Content
    }
}
