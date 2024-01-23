<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tables', static function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_activated')->default(true)->comment('是否可用');
            $table->unsignedTinyInteger('seat')->comment('最大座位數');
            $table->timestamps();
        });

        Schema::create('phones', static function (Blueprint $table): void {
            $table->id();
            $table->string('phone', 13);
            $table->boolean('is_blacklisted')->default(false)->comment('是否被封鎖');
            $table->timestamps();
        });
        Schema::create('queues', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 10)->comment('訂位人稱呼');
            $table->unsignedInteger('number')->comment('號碼牌號碼');
            $table->foreignId('phone_id')->constrained('phones');
            $table->foreignId('table_id')->nullable()->constrained('tables');
            $table->boolean('is_online')->comment('線上候位');
            $table->boolean('is_activated')->default(true)->comment('是否可用');
            $table->unsignedTinyInteger('seat')->comment('人數');
            $table->dateTime('booked_at')->comment('預計入座時間');
            $table->dateTime('check_in_at')->nullable()->comment('入座時間');
            $table->dateTime('check_out_at')->nullable()->comment('離座時間');
            $table->timestamps();
            // 黑名單判斷欄位
            $table->index(['phone_id', 'booked_at', 'check_in_at']);
        });

        Schema::table('queues', static function (Blueprint $table): void {
            // 建立索引避免重複號碼牌
            DB::statement(<<<SQL
            CREATE UNIQUE INDEX queues_date_booked_at_desc_number_unique on queues ((DATE(booked_at)) DESC, number)
            SQL
            );
        });

        Schema::create('phone_verifications', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('phone_id')->constrained('phones');
            $table->string('code', 6);
            $table->dateTime('expired_at')->comment('逾期時間');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('phones');
    }
};
