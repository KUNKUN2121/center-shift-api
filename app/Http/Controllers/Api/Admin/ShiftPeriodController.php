<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftPeriodController extends Controller
{
    // 募集期間一覧を取得(user用, openのもの)
    public function periodOpen()
    {
        // openのものを新しい順に取得
        $periods = ShiftPeriod::where('status', 'open')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->first();

        return response()->json($periods);
    }

    /**
     * 募集期間一覧を取得(admin用 すべての期間)
     */
    public function index()
    {
        // 新しい順に取得
        $periods = ShiftPeriod::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json($periods);
    }

    /**
     * 新規募集期間の作成
     */
    public function store(Request $request)
    {
        $request->validate([
            'year'     => 'required|integer',
            'month'    => 'required|integer|min:1|max:12',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'year.required'         => '年は必須です。',
            'year.integer'          => '年は整数で入力してください。',
            'month.required'        => '月は必須です。',
            'month.integer'         => '月は整数で入力してください。',
            'month.min'             => '月は1から12の間で入力してください。',
            'month.max'             => '月は1から12の間で入力してください。',
            'start_date.required'   => '開始日は必須です。',
            'start_date.date'       => '開始日は有効な日付を入力してください。',
            'end_date.required'     => '終了日は必須です。',
            'end_date.date'         => '終了日は有効な日付を入力してください。',
            'end_date.after_or_equal' => '終了日は開始日以降の日付を入力してください。',
        ]);



        // 重複チェック
        $exists = ShiftPeriod::where('year', $request->year)
            ->where('month', $request->month)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'この年月の募集は既に存在します。'], 422);
        }

        // start_dateとend_dateの妥当性チェック
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $period = ShiftPeriod::create([
            'year'       => $request->year,
            'month'      => $request->month,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'status'     => 'preparing', // 最初は準備中
        ]);

        return response()->json($period, 201);
    }

    /**
     * ステータスの更新 (例: 募集開始、締め切り)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:preparing,open,closed',
        ], [
            'status.required' => 'ステータスは必須です。',
            'status.in'       => 'ステータスは preparing, open, closed のいずれかを選択してください。',
        ]);

        // 募集開始時は、他にopenになっているところがある場合は弾く
        if ($request->status === 'open') {
            $openExists = ShiftPeriod::where('status', 'open')
                ->where('id', '!=', $id)
                ->exists();
            if ($openExists) {
                return response()->json(['message' => '他に募集中のものがあります。\n 募集状態にできるのは、一つだけです。 \n 閉じてから再度試してください。'], 422);
            }
        }

        $period = ShiftPeriod::findOrFail($id);
        $period->status = $request->status;
        $period->save();

        return response()->json($period);
    }

    /**
     * 詳細取得 (GET /api/admin/periods/{id})
     */
    public function show($id)
    {
        return ShiftPeriod::findOrFail($id);
    }

    /**
     * 詳細更新 (PUT /api/admin/periods/{id})
     */
    public function update(Request $request, $id)
    {
        $period = ShiftPeriod::findOrFail($id);

        $validated = $request->validate([
            'start_date'   => 'required|date', // 募集開始日
            'status'       => 'required|in:preparing,open,closed,published',
            'announcement' => 'nullable|string',
            'closed_days'  => 'nullable|array', // 休館日は配列で受け取る
            'closed_days.*'=> 'date',           // 中身は日付
        ]);

        $period->update($validated);

        return response()->json($period);
    }


/**
 * シフトの一括更新・作成・削除
 */
    public function updateBulkShifts(Request $request, $id)
    {
        $period = ShiftPeriod::findOrFail($id);

        // バリデーション
        $request->validate([
            'shifts' => 'array',
            'shifts.*.user_id' => 'required|exists:users,id',
            'shifts.*.start_datetime' => 'required|date',
            'shifts.*.end_datetime' => 'required|date|after:shifts.*.start_datetime',
        ]);

        // $start = Carbon::parse($data['start_datetime'])->toDateTimeString();
        // $end   = Carbon::parse($data['end_datetime'])->toDateTimeString();

        $incomingShifts = $request->input('shifts', []);

        DB::transaction(function () use ($period, $incomingShifts) {
            // 1. 送られてきたデータの中に存在するIDを抽出（数値のものだけ）
            $incomingIds = collect($incomingShifts)
                ->pluck('id')
                ->filter(fn($val) => is_numeric($val))
                ->toArray();

            // 2. 送られてきたリストにない既存のシフトを削除
            // (フロントエンドで消されたデータをDBからも消す)
            $period->shifts()->whereNotIn('id', $incomingIds)->delete();

            // 3. 各シフトデータの登録 or 更新
            foreach ($incomingShifts as $data) {
                $start = Carbon::parse($data['start_datetime'])->toDateTimeString();
                $end   = Carbon::parse($data['end_datetime'])->toDateTimeString();
                // フロントエンドから送られたIDが数値なら更新、それ以外（Math.random等）なら新規作成
                if (isset($data['id']) && is_numeric($data['id'])) {
                    Shift::where('id', $data['id'])->update([
                        'start_datetime' => $start,
                        'end_datetime'   => $end,
                        // user_id は通常変わらない想定ですが、必要なら更新に含めます
                    ]);
                } else {
                    $period->shifts()->create([
                        'user_id'        => $data['user_id'],
                        'start_datetime' => $data['start_datetime'],
                        'end_datetime'   => $data['end_datetime'],
                    ]);
                }
            }
        });

        return response()->json(['message' => 'シフトを保存しました。']);
    }
}
