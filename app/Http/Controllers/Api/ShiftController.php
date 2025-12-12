<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * 確定シフト一覧取得
     * GET /api/shifts?shift_period_id=1
     */
    public function index(Request $request)
    {
        $request->validate([
            'shift_period_id' => 'required|integer',
        ]);

        // 2. データ取得
        $shifts = Shift::query()
            ->with('user') // with使うと良いらしい
            ->where('shift_period_id', $request->shift_period_id)
            ->orderBy('start_datetime') // 日付順に並べる
            ->get();

        $data = $shifts->map(function ($shift) {
            return [
                'id'              => $shift->id,
                'shift_period_id' => $shift->shift_period_id,
                'user_id'         => $shift->user_id,
                'user_name'       => $shift->user->name,
                'start_datetime'  => $shift->start_datetime,
                'end_datetime'    => $shift->end_datetime,
            ];
        });

        return response()->json($data);
    }
}
