<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftPeriod;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * 確定シフト一覧取得
     * GET /api/shifts?shift_period_id=1
     * GET /api/shifts?yearmonth=202510
     */
    public function index(Request $request)
    {
        $request->validate([
            'shift_period_id' => 'nullable|integer',
            'yearmonth'      => 'nullable|digits:6',
        ]);

        // yearmonthが指定された場合、shift_period_idに変換する
        if ($request->filled('yearmonth')) {
            $year = substr($request->yearmonth, 0, 4);
            $month = substr($request->yearmonth, 4, 2);
            // かつ、statusがpublishedのものを取得する
            $period = ShiftPeriod::where('year', $year)
                ->where('month', $month)
                ->where('status', 'published')
                ->first();
            if ($period) {
                $request->merge(['shift_period_id' => $period->id]);
            } else {
                // 該当する公開済みシフト期間がない場合は空配列を返す
                return response()->json([], 200);
            }
        // shift_period_idが指定されていて、statusがpublishedでない場合もエラー
        } else if(
            $request->filled('shift_period_id') &&
            !ShiftPeriod::where('id', $request->shift_period_id)->where('status', 'published')->exists()
        ) {
            return response()->json(['error' => 'このシフトは公開されていません。'], 400);
        }
        else if(!$request->filled('shift_period_id')) {
            // shift_period_idもyearmonthも指定されていない場合はエラー
            return response()->json(['error' => 'shift_period_idまたはyearmonthを指定してください。'], 400);
        }

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
