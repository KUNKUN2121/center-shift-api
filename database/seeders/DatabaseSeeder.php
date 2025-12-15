<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\ShiftPeriod;
use App\Models\Submission;
use App\Models\Shift;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // 1. 管理者を作成
        User::create([
            'name' => '管理者 太郎',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. 一般ユーザーを3人作成
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[] = User::create([
                'name' => "スタッフ{$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'role' => 'user',
            ]);
        }

        // 3. シフト期間を作成 (2025年10月)
        $period = ShiftPeriod::create([
            'year' => 2025,
            'month' => 10,
            'start_date' => '2025-10-01',
            'end_date' => '2025-10-20',
            'status' => 'open', // 募集中
        ]);

        // 4. シフト希望を適当に登録
        // 各ユーザーごとに、ランダムな日付で希望を入れる
        foreach ($users as $user) {
            // 月の1日〜31日までループ
            for ($day = 1; $day <= 31; $day++) {
                // 50%の確率で「希望なし（休み）」にする
                if (rand(0, 1) === 0) continue;

                $date = Carbon::create(2025, 10, $day);

                // 土日は時間を変えるなどの演出
                if ($date->isWeekend()) {
                    $start = $date->copy()->setTime(10, 0); // 10:00
                    $end = $date->copy()->setTime(19, 0);   // 19:00
                } else {
                    $start = $date->copy()->setTime(17, 0); // 17:00
                    $end = $date->copy()->setTime(22, 0);   // 22:00
                }

                Submission::create([
                    'user_id' => $user->id,
                    'shift_period_id' => $period->id,
                    'start_datetime' => $start,
                    'end_datetime' => $end,
                    'notes' => rand(0, 4) === 0 ? '早上がり希望です' : null, // たまに備考を入れる
                ]);
            }
        }

        // 5. 確定シフトも少しだけ入れておく（管理者用テストのため）
        // user1 だけいくつか確定させておく
        $targetUser = $users[0];
        $shiftsToCreate = [5, 6, 7]; // 5,6,7日だけ確定
        foreach ($shiftsToCreate as $day) {
            $date = Carbon::create(2025, 10, $day);
            Shift::create([
                'user_id' => $targetUser->id,
                'shift_period_id' => $period->id,
                'start_datetime' => $date->copy()->setTime(17, 0),
                'end_datetime' => $date->copy()->setTime(22, 0),
                'notes' => '確定済',
            ]);
        }
    }
}
