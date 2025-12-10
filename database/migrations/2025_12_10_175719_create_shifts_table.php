<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_period_id')->constrained()->cascadeOnDelete();

            $table->dateTime('start_datetime')->comment('確定開始日時');
            $table->dateTime('end_datetime')->comment('確定終了日時');

            $table->text('notes')->nullable()->comment('管理者メモ');
            $table->timestamps();

            // 期間検索用インデックス
            $table->index(['start_datetime', 'end_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
