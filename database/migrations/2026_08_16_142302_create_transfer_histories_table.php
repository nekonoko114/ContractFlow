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
        Schema::create('transfer_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('line_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone_number')->nullable();
            $table->string('line_name')->default('メイン回線');
            $table->string('contract_holder')->nullable();
            $table->string('actual_user')->nullable();
            
            // 乗り換え元 (MNP元)
            $table->string('from_carrier_name');
            $table->string('from_plan_name')->nullable();
            $table->integer('from_monthly_fee')->default(0);
            $table->decimal('from_data_capacity', 8, 2)->default(0);
            
            // 乗り換え先 (MNP先)
            $table->string('to_carrier_name');
            $table->string('to_plan_name')->nullable();
            $table->integer('to_monthly_fee')->default(0);
            $table->decimal('to_data_capacity', 8, 2)->default(0);
            
            // 乗り換え情報 & 効果
            $table->date('transfer_date');
            $table->string('usage_period_text')->nullable(); // 例: "1年2ヶ月"
            $table->integer('usage_period_months')->default(0); // 利用月数
            $table->integer('monthly_saving')->default(0); // 月額節約額 (from - to)
            $table->integer('annual_saving')->default(0); // 年間節約額 (monthly_saving * 12)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_histories');
    }
};
