<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('shift_periods', function (Blueprint $table) {
            $table->text('announcement')->nullable()->after('status'); // アナウンス
            $table->json('closed_days')->nullable()->after('announcement'); // 休館日
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_periods', function (Blueprint $table) {
            $table->dropColumn('announcement');
            $table->dropColumn('closed_days');
        });
    }
};
