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
        Schema::table('carriers', function (Blueprint $table) {
            $table->integer('safe_period_days')->default(180)->after('type')->comment('推奨最低維持日数（BL防止目安）');
            $table->string('brand_color_bg')->nullable()->after('safe_period_days')->comment('ブランド背景色クラス');
            $table->string('brand_color_text')->nullable()->after('brand_color_bg')->comment('ブランド文字色クラス');
            $table->string('brand_color_border')->nullable()->after('brand_color_text')->comment('ブランドボーダー色クラス');
        });

        Schema::table('lines', function (Blueprint $table) {
            $table->integer('custom_safe_period_days')->nullable()->after('status')->comment('回線ごとの個別安全日数（指定時優先）');
        });

        Schema::table('transfer_histories', function (Blueprint $table) {
            $table->integer('device_cost')->default(0)->after('annual_saving')->comment('端末購入費用');
            $table->integer('cashback_amount')->default(0)->after('device_cost')->comment('キャッシュバック・ポイント還元額');
            $table->integer('admin_fee')->default(3850)->after('cashback_amount')->comment('契約事務手数料等');
            $table->integer('device_sale_profit')->default(0)->after('admin_fee')->comment('旧端末売却益等');
            $table->integer('total_net_saving')->default(0)->after('device_sale_profit')->comment('初年度トータル実質収支（削減額+還元-端末代-手数料）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn(['safe_period_days', 'brand_color_bg', 'brand_color_text', 'brand_color_border']);
        });

        Schema::table('lines', function (Blueprint $table) {
            $table->dropColumn(['custom_safe_period_days']);
        });

        Schema::table('transfer_histories', function (Blueprint $table) {
            $table->dropColumn(['device_cost', 'cashback_amount', 'admin_fee', 'device_sale_profit', 'total_net_saving']);
        });
    }
};
