<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftPeriod;
use App\Models\Shift;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;

class ShiftEditorController extends Controller
{
    /**
     * エディター画面用の一括データ取得
     * GET /api/admin/periods/{id}/editor
     */
    public function show($periodId)
    {
        // 1. 期間情報の取得
        $period = ShiftPeriod::findOrFail($periodId);

        // 2. ユーザー一覧 (全スタッフ)
        $users = User::orderBy('id')->get();

        // 3. 確定シフト一覧 (shifts)
        $shifts = Shift::with('user')
            ->where('shift_period_id', $periodId)
            ->get()
            ->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'user_id' => $shift->user_id,
                    'shift_period_id' => $shift->shift_period_id,
                    'start_datetime' => $shift->start_datetime,
                    'end_datetime' => $shift->end_datetime,
                ];
            });

        // 4. ユーザー提出一覧 (Submissions)
        $submissions = Submission::with('user')
            ->where('shift_period_id', $periodId)
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'user_id' => $sub->user_id,
                    'start_datetime' => $sub->start_datetime,
                    'end_datetime' => $sub->end_datetime,
                    'status' => $sub->status,
                    'notes' => $sub->notes,
                ];
            });

        return response()->json([
            'period'      => $period,
            'users'       => $users,
            'shifts'      => $shifts,
            'submissions' => $submissions,
        ]);
    }
}
