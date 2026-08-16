<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Line;
use App\Models\TransferHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. 主要キャリアの登録
        $carriers = [
            ['name' => 'NTTドコモ', 'code' => 'docomo', 'type' => 'MNO', 'safe_period_days' => 180, 'brand_color_bg' => 'bg-red-50', 'brand_color_text' => 'text-red-700', 'brand_color_border' => 'border-red-200', 'badge_color' => 'red', 'display_order' => 1],
            ['name' => 'au', 'code' => 'au', 'type' => 'MNO', 'safe_period_days' => 211, 'brand_color_bg' => 'bg-orange-50', 'brand_color_text' => 'text-orange-700', 'brand_color_border' => 'border-orange-200', 'badge_color' => 'orange', 'display_order' => 2],
            ['name' => 'SoftBank', 'code' => 'softbank', 'type' => 'MNO', 'safe_period_days' => 180, 'brand_color_bg' => 'bg-slate-100', 'brand_color_text' => 'text-slate-800', 'brand_color_border' => 'border-slate-300', 'badge_color' => 'slate', 'display_order' => 3],
            ['name' => '楽天モバイル', 'code' => 'rakuten', 'type' => 'MNO', 'safe_period_days' => 180, 'brand_color_bg' => 'bg-fuchsia-50', 'brand_color_text' => 'text-fuchsia-700', 'brand_color_border' => 'border-fuchsia-200', 'badge_color' => 'pink', 'display_order' => 4],
            ['name' => 'ahamo', 'code' => 'ahamo', 'type' => 'Online', 'safe_period_days' => 180, 'brand_color_bg' => 'bg-emerald-50', 'brand_color_text' => 'text-emerald-700', 'brand_color_border' => 'border-emerald-200', 'badge_color' => 'blue', 'display_order' => 5],
            ['name' => 'povo', 'code' => 'povo', 'type' => 'Online', 'safe_period_days' => 180, 'brand_color_bg' => 'bg-amber-50', 'brand_color_text' => 'text-amber-800', 'brand_color_border' => 'border-amber-200', 'badge_color' => 'amber', 'display_order' => 6],
            ['name' => 'LINEMO', 'code' => 'linemo', 'type' => 'Online', 'safe_period_days' => 180, 'brand_color_bg' => 'bg-lime-50', 'brand_color_text' => 'text-lime-700', 'brand_color_border' => 'border-lime-200', 'badge_color' => 'emerald', 'display_order' => 7],
            ['name' => 'Y!mobile', 'code' => 'ymobile', 'type' => 'SubBrand', 'safe_period_days' => 180, 'brand_color_bg' => 'bg-rose-50', 'brand_color_text' => 'text-rose-700', 'brand_color_border' => 'border-rose-200', 'badge_color' => 'red', 'display_order' => 8],
            ['name' => 'UQ mobile', 'code' => 'uqmobile', 'type' => 'SubBrand', 'safe_period_days' => 211, 'brand_color_bg' => 'bg-sky-50', 'brand_color_text' => 'text-sky-700', 'brand_color_border' => 'border-sky-200', 'badge_color' => 'sky', 'display_order' => 9],
            ['name' => 'mineo', 'code' => 'mineo', 'type' => 'MVNO', 'safe_period_days' => 90, 'brand_color_bg' => 'bg-teal-50', 'brand_color_text' => 'text-teal-700', 'brand_color_border' => 'border-teal-200', 'badge_color' => 'emerald', 'display_order' => 10],
            ['name' => 'IIJmio', 'code' => 'iijmio', 'type' => 'MVNO', 'safe_period_days' => 90, 'brand_color_bg' => 'bg-indigo-50', 'brand_color_text' => 'text-indigo-700', 'brand_color_border' => 'border-indigo-200', 'badge_color' => 'indigo', 'display_order' => 11],
            ['name' => '日本通信SIM', 'code' => 'nihontsu', 'type' => 'MVNO', 'safe_period_days' => 90, 'brand_color_bg' => 'bg-blue-50', 'brand_color_text' => 'text-blue-700', 'brand_color_border' => 'border-blue-200', 'badge_color' => 'blue', 'display_order' => 12],
            ['name' => 'イオンモバイル', 'code' => 'aeon', 'type' => 'MVNO', 'safe_period_days' => 90, 'brand_color_bg' => 'bg-pink-50', 'brand_color_text' => 'text-pink-700', 'brand_color_border' => 'border-pink-200', 'badge_color' => 'rose', 'display_order' => 13],
            ['name' => 'NUROモバイル', 'code' => 'nuro', 'type' => 'MVNO', 'safe_period_days' => 90, 'brand_color_bg' => 'bg-purple-50', 'brand_color_text' => 'text-purple-700', 'brand_color_border' => 'border-purple-200', 'badge_color' => 'purple', 'display_order' => 14],
            ['name' => 'その他', 'code' => 'other', 'type' => 'Other', 'safe_period_days' => 180, 'brand_color_bg' => 'bg-gray-50', 'brand_color_text' => 'text-gray-700', 'brand_color_border' => 'border-gray-200', 'badge_color' => 'gray', 'display_order' => 99],
        ];

        foreach ($carriers as $carrier) {
            Carrier::updateOrCreate(['code' => $carrier['code']], $carrier);
        }

        // 2. デモユーザーの作成
        $user = User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => '山田 太郎',
                'password' => Hash::make('password123'),
            ]
        );

        // 3. サンプル回線データの作成
        $docomo = Carrier::where('code', 'docomo')->first();
        $ahamo = Carrier::where('code', 'ahamo')->first();
        $uq = Carrier::where('code', 'uqmobile')->first();
        $rakuten = Carrier::where('code', 'rakuten')->first();

        $line1 = Line::create([
            'user_id' => $user->id,
            'carrier_id' => $ahamo ? $ahamo->id : null,
            'line_name' => 'メイン回線 (iPhone 15 Pro)',
            'phone_number' => '090-1234-5678',
            'contract_holder' => '山田 太郎',
            'actual_user' => '山田 太郎',
            'network_pin' => '4982',
            'carrier_name' => 'ahamo',
            'plan_name' => 'ahamo 30GBプラン (大盛りなし)',
            'monthly_fee' => 2970,
            'data_capacity' => 30.0,
            'contract_start_date' => Carbon::now()->subMonths(14)->subDays(12),
            'status' => 'active',
            'notes' => '5分通話無料付き。ドコモ光セット割なしでもお得。',
        ]);

        $line2 = Line::create([
            'user_id' => $user->id,
            'carrier_id' => $uq ? $uq->id : null,
            'line_name' => 'サブ回線 (仕事用 Pixel 8)',
            'phone_number' => '080-9876-5432',
            'contract_holder' => '山田 太郎',
            'actual_user' => '山田 太郎',
            'network_pin' => '1024',
            'carrier_name' => 'UQ mobile',
            'plan_name' => 'コミコミプラン+',
            'monthly_fee' => 3278,
            'data_capacity' => 33.0,
            'contract_start_date' => Carbon::now()->subMonths(6)->subDays(5),
            'status' => 'active',
            'notes' => '10分通話定額込み。仕事用テザリングメイン。',
        ]);

        $line3 = Line::create([
            'user_id' => $user->id,
            'carrier_id' => $rakuten ? $rakuten->id : null,
            'line_name' => '家族回線 (長男 スマホ)',
            'phone_number' => '070-5555-4444',
            'contract_holder' => '山田 太郎',
            'actual_user' => '山田 一郎 (長男)',
            'network_pin' => '8823',
            'carrier_name' => '楽天モバイル',
            'plan_name' => 'Rakuten最強プラン (〜3GB)',
            'monthly_fee' => 1078,
            'data_capacity' => 3.0,
            'contract_start_date' => Carbon::now()->subMonths(3)->subDays(20),
            'status' => 'active',
            'notes' => 'Rakuten Linkで通話無料。子供用フィルタリング設定済み。',
        ]);

        // 4. サンプル乗り換え履歴
        TransferHistory::create([
            'user_id' => $user->id,
            'line_id' => $line1->id,
            'phone_number' => '090-1234-5678',
            'line_name' => 'メイン回線 (iPhone 15 Pro)',
            'contract_holder' => '山田 太郎',
            'actual_user' => '山田 太郎',
            'from_carrier_name' => 'NTTドコモ',
            'from_plan_name' => 'eximo (〜無制限)',
            'from_monthly_fee' => 7315,
            'from_data_capacity' => 60.0,
            'to_carrier_name' => 'ahamo',
            'to_plan_name' => 'ahamo 30GB',
            'to_monthly_fee' => 2970,
            'to_data_capacity' => 30.0,
            'transfer_date' => Carbon::now()->subMonths(14)->subDays(12),
            'usage_period_text' => '2年4ヶ月',
            'usage_period_months' => 28,
            'monthly_saving' => 4345,
            'annual_saving' => 52140,
            'notes' => 'ギガを30GBに絞り月額4,345円（年間52,140円）の大幅削減に成功。',
        ]);

        TransferHistory::create([
            'user_id' => $user->id,
            'line_id' => $line2->id,
            'phone_number' => '080-9876-5432',
            'line_name' => 'サブ回線 (仕事用 Pixel 8)',
            'contract_holder' => '山田 太郎',
            'actual_user' => '山田 太郎',
            'from_carrier_name' => 'au',
            'from_plan_name' => '使い放題MAX 5G',
            'from_monthly_fee' => 7238,
            'from_data_capacity' => 50.0,
            'to_carrier_name' => 'UQ mobile',
            'to_plan_name' => 'コミコミプラン+',
            'to_monthly_fee' => 3278,
            'to_data_capacity' => 33.0,
            'transfer_date' => Carbon::now()->subMonths(6)->subDays(5),
            'usage_period_text' => '1年8ヶ月',
            'usage_period_months' => 20,
            'monthly_saving' => 3960,
            'annual_saving' => 47520,
            'notes' => 'auからUQ mobileへの番号移行。通信品質はそのままに月額半額以下に。',
        ]);
    }
}
