<?php

namespace App\Http\Requests;

use App\Models\ShiftPeriod;
use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ログイン済み OK（middlewareで制御済み）
    }

    public function rules(): array
    {
        return [
            'shift_period_id' => 'required|integer|exists:shift_periods,id',
            'start_datetime'  => 'required|date',
            'end_datetime'    => 'required|date|after:start_datetime',
            'notes'           => 'nullable|string|max:255',
        ];
    }

    /**
     * 追加のバリデーションロジック
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 入力データの取得
            $periodId = $this->input('shift_period_id');
            // 更新時(PUT)はURLパラメータからIDを取る必要がある場合があるが、
            // 今回は submissions/{id} なので $this->route('submission') でIDが取れるか確認
            // ※ ルート定義が /submissions/{id} なので、パラメータ名は 'id' かもしれません。
            //   php artisan route:list で確認できますが、通常はパラメータ名になります。
            $ignoreId = $this->route('id');

            $start = Carbon::parse($this->input('start_datetime'));
            $end   = Carbon::parse($this->input('end_datetime'));

            // --------------------------------------
            // 1. 期間チェック (ShiftPeriodの年月と合っているか？)
            // --------------------------------------
            $period = ShiftPeriod::find($periodId);
            if ($period) {
                // 期間の開始日と終了日を計算
                $periodStart = Carbon::create($period->year, $period->month, 1)->startOfDay();
                $periodEnd   = Carbon::create($period->year, $period->month, 1)->endOfMonth()->endOfDay();

                if (!$start->between($periodStart, $periodEnd) || !$end->between($periodStart, $periodEnd)) {
                    $validator->errors()->add('start_datetime', '指定されたシフト期間外の日付が含まれています。');
                }
            } else {
                $validator->errors()->add('shift_period_id', '指定されたシフト期間が存在しません。');
            }

            // --------------------------------------
            // 2. 重複チェック (自分の他のシフトと時間が被っていないか？)
            // --------------------------------------
            // 条件: (開始A < 終了B) AND (終了A > 開始B)
            $exists = Submission::query()
                ->where('user_id', Auth::id())
                // 更新の場合は、自分自身を除外する
                ->when($ignoreId, function ($query, $id) {
                    $query->where('id', '!=', $id);
                })
                ->where(function ($query) use ($start, $end) {
                    $query->where('start_datetime', '<', $end)
                          ->where('end_datetime', '>', $start);
                })
                ->exists();

            if ($exists) {
                $validator->errors()->add('start_datetime', 'この時間帯には既にシフト希望が登録されています。');
            }
        });
    }
}
