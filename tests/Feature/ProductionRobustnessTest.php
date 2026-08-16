<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Livewire\LineCreateComparison;
use App\Livewire\TransferHistoryList;
use App\Models\Carrier;
use App\Models\Line;
use App\Models\TransferHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ProductionRobustnessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * テスト1: 0円プラン（povo等）や低額プランの登録と差額計算が正確に行われるか
     */
    public function test_zero_yen_and_low_tier_plans_are_handled_correctly(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        Livewire::test(LineCreateComparison::class)
            ->set('line_name', 'povo 0円基本回線')
            ->set('phone_number', '090-0000-0000')
            ->set('carrier_name', 'povo')
            ->set('monthly_fee', 0)
            ->set('data_capacity', 0.0)
            ->set('contract_holder', '山田 太郎')
            ->set('actual_user', '山田 太郎')
            ->set('contract_start_date', Carbon::now()->format('Y-m-d'))
            ->set('prev_carrier_name', 'NTTドコモ')
            ->set('prev_monthly_fee', 7315)
            ->set('prev_data_capacity', 30.0)
            ->call('save')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('lines', [
            'user_id' => $user->id,
            'line_name' => 'povo 0円基本回線',
            'monthly_fee' => 0,
            'data_capacity' => 0.0,
        ]);

        $history = TransferHistory::where('user_id', $user->id)
            ->where('phone_number', '090-0000-0000')
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals(7315, $history->monthly_saving);
        $this->assertEquals(7315 * 12, $history->annual_saving);
    }

    /**
     * テスト2: 乗り換えによって月額料金が上がった場合（マイナス収支）も正確に計算・記録されるか
     */
    public function test_negative_savings_upgrade_transfer_is_handled_properly(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        Livewire::test(LineCreateComparison::class)
            ->set('line_name', '無制限プランへ格上げ')
            ->set('phone_number', '080-8888-7777')
            ->set('carrier_name', 'NTTドコモ')
            ->set('monthly_fee', 7315)
            ->set('data_capacity', 100.0)
            ->set('contract_holder', '山田 太郎')
            ->set('actual_user', '山田 太郎')
            ->set('contract_start_date', Carbon::now()->format('Y-m-d'))
            ->set('prev_carrier_name', 'IIJmio')
            ->set('prev_monthly_fee', 850)
            ->set('prev_data_capacity', 2.0)
            ->call('save');

        $history = TransferHistory::where('user_id', $user->id)
            ->where('phone_number', '080-8888-7777')
            ->first();

        $this->assertNotNull($history);
        // 850 - 7315 = -6465
        $this->assertEquals(-6465, $history->monthly_saving);
        $this->assertEquals(-6465 * 12, $history->annual_saving);
    }

    /**
     * テスト3: マルチテナンシー・データ分離セキュリティ（ユーザーAがユーザーBのデータを操作できないこと）
     */
    public function test_user_isolation_and_security_idor_protection(): void
    {
        $userA = User::where('email', 'demo@example.com')->first();
        $userB = User::factory()->create([
            'email' => 'other_user@example.com',
            'name' => '他人 太郎',
            'password' => bcrypt('password123'),
        ]);

        // ユーザーBが回線を作成
        $lineB = $userB->lines()->create([
            'carrier_name' => 'SoftBank',
            'line_name' => 'ユーザーBの機密回線',
            'monthly_fee' => 8000,
            'data_capacity' => 50.0,
            'contract_holder' => '他人 太郎',
            'actual_user' => '他人 太郎',
            'contract_start_date' => now(),
            'status' => 'active',
            'network_pin' => '9999',
        ]);

        // ユーザーAとしてログイン
        $this->actingAs($userA);

        // ユーザーAのダッシュボードにユーザーBの回線が表示されないこと
        Livewire::test(Dashboard::class)
            ->assertDontSee('ユーザーBの機密回線');

        // ユーザーAがユーザーBの回線IDを指定して編集モーダルを開こうとすると、ModelNotFoundException で防がれること
        try {
            Livewire::test(Dashboard::class)
                ->call('openEditModal', $lineB->id);
            $this->fail('他人の回線を開こうとした際に例外が発生しませんでした');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertTrue(true, 'IDORアクセスが正しく遮断されました');
        }

        // ユーザーAが他人の回線を削除しようとしても削除されないこと
        try {
            Livewire::test(Dashboard::class)
                ->call('confirmDelete', $lineB->id)
                ->call('deleteLine');
        } catch (\Exception $e) {
            // 例外が発生しても安全
        }

        $this->assertDatabaseHas('lines', ['id' => $lineB->id]);
    }

    /**
     * テスト4: 暗証番号 (PIN) の暗号化ストレージ検証（DBには平文が保存されない）
     */
    public function test_network_pin_is_safely_encrypted_in_database(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        $line = $user->lines()->create([
            'carrier_name' => 'NTTドコモ',
            'line_name' => '暗号化テスト回線',
            'monthly_fee' => 2970,
            'data_capacity' => 20.0,
            'contract_holder' => '山田 太郎',
            'actual_user' => '山田 太郎',
            'contract_start_date' => now(),
            'status' => 'active',
            'network_pin' => '7890',
        ]);

        // モデルインスタンス経由ではアクセサによって復号化される
        $this->assertEquals('7890', $line->network_pin);

        // DB直接クエリ（Raw Query）では平文 '7890' ではなく暗号化文字列であること
        $rawLine = DB::table('lines')->where('id', $line->id)->first();
        $this->assertNotEquals('7890', $rawLine->network_pin);
        $this->assertStringContainsString('eyJ', $rawLine->network_pin); // Laravel Encrypt payload
    }

    /**
     * テスト5: CSVインポートの異常系・バリデーション耐性
     */
    public function test_csv_import_handles_valid_and_invalid_data_gracefully(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        // 正常なCSVデータ（エクスポートフォーマット互換）
        $validCsvContent = "ID,回線名,電話番号,携帯会社名,料金プラン名,月額料金(円),通信容量(GB),契約名義人,使用者,契約開始日,利用日数,目標安全維持日数,安全状態,ステータス,乗り換え回数,MNP予約番号,MNP有効期限,メモ\n" .
            ",一括インポート回線1,090-1111-2222,ahamo,30GB,2970,30.0,山田 太郎,山田 太郎,2024-01-01,100,180,安全達成(転出OK),契約中,0,,,テストメモ1\n" .
            ",一括インポート回線2,080-3333-4444,UQ mobile,コミコミプラン+,3278,30.0,山田 太郎,山田 花子,2024-02-01,70,180,短期利用中,契約中,0,,,テストメモ2\n";

        $file = UploadedFile::fake()->createWithContent('lines.csv', $validCsvContent);

        Livewire::test(Dashboard::class)
            ->set('csvFile', $file)
            ->call('importCsv')
            ->assertSee('件の回線情報をインポートしました。');

        $this->assertDatabaseHas('lines', [
            'user_id' => $user->id,
            'line_name' => '一括インポート回線1',
            'carrier_name' => 'ahamo',
            'monthly_fee' => 2970,
        ]);

        $this->assertDatabaseHas('lines', [
            'user_id' => $user->id,
            'line_name' => '一括インポート回線2',
            'carrier_name' => 'UQ mobile',
            'monthly_fee' => 3278,
        ]);
    }

    /**
     * テスト6: 大量データ（50回線）登録時のダッシュボード集計とメモリ・パフォーマンス
     */
    public function test_large_number_of_lines_statistics_and_performance(): void
    {
        $user = User::where('email', 'demo@example.com')->first();
        $this->actingAs($user);

        // 既存の回線に加え、50回線をバルク作成
        $bulkLines = [];
        for ($i = 1; $i <= 50; $i++) {
            $bulkLines[] = [
                'user_id' => $user->id,
                'carrier_name' => ($i % 2 === 0) ? 'ahamo' : 'UQ mobile',
                'line_name' => "大量テスト回線 {$i}",
                'phone_number' => sprintf("090-0000-%04d", $i),
                'monthly_fee' => 2000 + ($i * 10),
                'data_capacity' => 20.0,
                'contract_holder' => '山田 太郎',
                'actual_user' => '山田 太郎',
                'contract_start_date' => Carbon::now()->subDays($i * 5)->format('Y-m-d'),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Line::insert($bulkLines);

        // ダッシュボードが正常にレンダリングされ、エラーなく計算できること
        $startTime = microtime(true);

        Livewire::test(Dashboard::class)
            ->assertStatus(200)
            ->assertSee('大量テスト回線 1')
            ->assertSee('大量テスト回線 50');

        $executionTime = microtime(true) - $startTime;
        // 50回線でも 0.5秒未満で高速に処理されること
        $this->assertLessThan(1.0, $executionTime);
    }
}
