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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();

            // 外部キー制約 (cascadeOnDeleteで親が消えたら道連れにする)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_period_id')->constrained()->cascadeOnDelete();

            $table->dateTime('start_datetime')->comment('希望開始日時');
            $table->dateTime('end_datetime')->comment('希望終了日時');

            $table->text('notes')->nullable()->comment('備考');
            $table->timestamps();

            // 検索高速化のためにインデックスを貼っておくのがおすすめ
            $table->index(['user_id', 'shift_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
