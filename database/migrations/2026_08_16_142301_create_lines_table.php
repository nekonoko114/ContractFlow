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
        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('line_name')->default('メイン回線');
            $table->string('phone_number')->nullable();
            $table->string('contract_holder'); // 契約名義人
            $table->string('actual_user'); // 使用者
            $table->text('network_pin')->nullable(); // 暗証番号 (暗号化保存)
            $table->string('carrier_name'); // キャリア名 (NTTドコモ, au, 楽天モバイル, ahamo 等)
            $table->string('plan_name')->nullable(); // プラン名
            $table->integer('monthly_fee')->default(0); // 月額料金 (円)
            $table->decimal('data_capacity', 8, 2)->default(0); // 通信容量 (GB)
            $table->date('contract_start_date'); // 契約開始日
            $table->string('status')->default('active'); // active (利用中), reserved (MNP予約中), transferred (乗り換え済み), cancelled (解約)
            $table->string('mnp_reservation_number')->nullable(); // MNP予約番号
            $table->date('mnp_reservation_expire_date')->nullable(); // MNP予約有効期限
            $table->text('notes')->nullable(); // メモ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lines');
    }
};
