<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\LineCreateComparison;
use App\Livewire\TransferHistoryList;
use App\Models\Carrier;
use App\Models\Line;
use App\Models\TransferHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LineManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('ContractFlow');
    }

    public function test_user_can_login(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'demo@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_dashboard_renders_lines_and_calculates_usage_period(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('ahamo');
        $response->assertSee('UQ mobile');
        $response->assertSee('山田 太郎');

        // 暗証番号の暗号化検証
        $line = $user->lines()->first();
        $this->assertEquals('4982', $line->network_pin);
    }

    public function test_user_can_create_new_line_with_comparison(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        Livewire::test(LineCreateComparison::class)
            ->set('line_name', '新規 iPad 回線')
            ->set('phone_number', '090-9999-8888')
            ->set('carrier_name', 'povo')
            ->set('monthly_fee', 990)
            ->set('data_capacity', 3.0)
            ->set('contract_holder', '山田 太郎')
            ->set('actual_user', '山田 太郎')
            ->set('network_pin', '5678')
            ->set('contract_start_date', Carbon::now()->format('Y-m-d'))
            ->set('prev_carrier_name', 'NTTドコモ')
            ->set('prev_monthly_fee', 4500)
            ->set('prev_data_capacity', 5.0)
            ->call('save')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('lines', [
            'user_id' => $user->id,
            'line_name' => '新規 iPad 回線',
            'carrier_name' => 'povo',
            'monthly_fee' => 990,
        ]);

        $this->assertDatabaseHas('transfer_histories', [
            'user_id' => $user->id,
            'line_name' => '新規 iPad 回線',
            'from_carrier_name' => 'NTTドコモ',
            'to_carrier_name' => 'povo',
            'monthly_saving' => 3510,
        ]);
    }

    public function test_user_can_transfer_existing_line(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        /** @var Line $line */
        $line = $user->lines()->where('carrier_name', 'UQ mobile')->first();

        Livewire::test(LineCreateComparison::class, ['from_line_id' => $line->id])
            ->set('carrier_name', 'LINEMO')
            ->set('monthly_fee', 990)
            ->set('data_capacity', 3.0)
            ->call('save')
            ->assertRedirect(route('dashboard'));

        // 回線が LINEMO に更新されていること
        $line->refresh();
        $this->assertEquals('LINEMO', $line->carrier_name);
        $this->assertEquals(990, $line->monthly_fee);

        // 乗り換え履歴が作成されていること
        $this->assertDatabaseHas('transfer_histories', [
            'user_id' => $user->id,
            'line_id' => $line->id,
            'from_carrier_name' => 'UQ mobile',
            'to_carrier_name' => 'LINEMO',
        ]);
    }

    public function test_transfer_history_screen_can_be_rendered(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        $response = $this->get('/history');
        $response->assertStatus(200);
        $response->assertSee('乗り換え履歴');
        $response->assertSee('eximo');
    }

    public function test_dashboard_search_and_filter(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->set('search', 'Pixel')
            ->assertSee('サブ回線')
            ->assertDontSee('メイン回線')
            ->set('search', '')
            ->set('carrierFilter', '楽天モバイル')
            ->assertSee('家族回線')
            ->assertDontSee('サブ回線');
    }

    public function test_user_can_edit_line(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        /** @var Line $line */
        $line = $user->lines()->first();

        Livewire::test(Dashboard::class)
            ->call('openEditModal', $line->id)
            ->set('edit_line_name', '更新後の回線名')
            ->set('edit_monthly_fee', 2178)
            ->call('updateLine')
            ->assertSee('回線情報を更新しました。');

        $line->refresh();
        $this->assertEquals('更新後の回線名', $line->line_name);
        $this->assertEquals(2178, $line->monthly_fee);
    }

    public function test_user_can_delete_line(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        /** @var Line $line */
        $line = $user->lines()->create([
            'carrier_name' => 'テストキャリア',
            'line_name' => '削除予定回線',
            'monthly_fee' => 1000,
            'data_capacity' => 5.0,
            'contract_holder' => 'テスト太郎',
            'actual_user' => 'テスト太郎',
            'contract_start_date' => now(),
            'status' => 'active',
        ]);

        Livewire::test(Dashboard::class)
            ->call('confirmDelete', $line->id)
            ->call('deleteLine')
            ->assertSee('回線を削除しました。');

        $this->assertDatabaseMissing('lines', ['id' => $line->id]);
    }

    public function test_transfer_count_and_carrier_stats_are_displayed(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSee('キャリア別 契約・乗り換え実績カウント')
            ->assertSee('電話番号別 乗り換え回数・キャリア変遷')
            ->assertSee('090-1234-5678')
            ->assertSee('乗り換え');
    }

    public function test_phone_number_specific_transfer_count(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        /** @var Line $mainLine */
        $mainLine = $user->lines()->where('phone_number', '090-1234-5678')->first();
        $this->assertNotNull($mainLine);
        $this->assertEquals(1, $mainLine->transfer_count);

        /** @var Line $familyLine */
        $familyLine = $user->lines()->where('phone_number', '070-5555-4444')->first();
        $this->assertNotNull($familyLine);
        $this->assertEquals(0, $familyLine->transfer_count);
    }

    public function test_safe_period_accessors(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        // 200日前に契約した回線（標準180日設定） -> 安全達成
        $line1 = $user->lines()->create([
            'carrier_name' => 'ahamo',
            'line_name' => '長期回線',
            'monthly_fee' => 2970,
            'data_capacity' => 30.0,
            'contract_holder' => 'テスト太郎',
            'actual_user' => 'テスト太郎',
            'contract_start_date' => Carbon::now()->subDays(200),
            'status' => 'active',
        ]);

        $this->assertEquals(180, $line1->target_safe_period_days);
        $this->assertEquals(200, $line1->usage_days);
        $this->assertEquals(0, $line1->safe_period_remaining_days);
        $this->assertEquals(100, $line1->safe_period_progress);
        $this->assertEquals('safe', $line1->safe_status);

        // 30日前に契約した回線（標準180日設定） -> 短期利用中
        $line2 = $user->lines()->create([
            'carrier_name' => 'ahamo',
            'line_name' => '短期回線',
            'monthly_fee' => 2970,
            'data_capacity' => 30.0,
            'contract_holder' => 'テスト太郎',
            'actual_user' => 'テスト太郎',
            'contract_start_date' => Carbon::now()->subDays(30),
            'status' => 'active',
        ]);

        $this->assertEquals(150, $line2->safe_period_remaining_days);
        $this->assertEquals('danger', $line2->safe_status);
    }

    public function test_total_net_saving_simulation_and_creation(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        Livewire::test(LineCreateComparison::class)
            ->set('line_name', 'CB付き乗り換え回線')
            ->set('phone_number', '080-1111-2222')
            ->set('carrier_name', 'UQ mobile')
            ->set('monthly_fee', 2178)
            ->set('data_capacity', 20.0)
            ->set('contract_holder', '山田 太郎')
            ->set('actual_user', '山田 太郎')
            ->set('contract_start_date', Carbon::now()->format('Y-m-d'))
            ->set('prev_carrier_name', 'au')
            ->set('prev_monthly_fee', 7000)
            ->set('prev_data_capacity', 20.0)
            ->set('device_cost', 1)
            ->set('cashback_amount', 20000)
            ->set('admin_fee', 3850)
            ->set('device_sale_profit', 15000)
            ->call('save');

        // 月額削減: 7000 - 2178 = 4822
        // 年間月額削減: 4822 * 12 = 57864
        // トータル実質収支: 57864 + 20000 + 15000 - 1 - 3850 = 89013
        $history = TransferHistory::where('user_id', $user->id)
            ->where('phone_number', '080-1111-2222')
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals(4822, $history->monthly_saving);
        $this->assertEquals(57864, $history->annual_saving);
        $this->assertEquals(89013, $history->total_net_saving);
    }

    public function test_csv_export_can_be_executed(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->call('exportCsv')
            ->assertFileDownloaded();
    }
}
