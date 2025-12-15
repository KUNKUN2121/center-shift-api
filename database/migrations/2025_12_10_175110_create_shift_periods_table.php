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
        Schema::create('shift_periods', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->comment('年 (例: 2025)');
            $table->integer('month')->comment('月 (例: 10)');

            $table->dateTime('start_date')->comment('募集開始');
            $table->dateTime('end_date')->comment('募集終了');

            $table->string('status')->default('preparing')
                  ->comment('状態(preparing/open/closed/published)');

            $table->timestamps();

            $table->unique(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_periods');
    }
};
