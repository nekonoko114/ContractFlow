<?php

namespace App\Livewire;

use App\Models\Carrier;
use App\Models\Line;
use App\Models\TransferHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('新規登録・乗り換え比較 - ContractFlow')]
class LineCreateComparison extends Component
{
    #[Url]
    public ?int $from_line_id = null;

    // 新契約情報
    public string $line_name = 'メイン回線';
    public string $phone_number = '';
    public string $carrier_name = 'ahamo';
    public ?int $carrier_id = null;
    public string $plan_name = '';
    public int $monthly_fee = 2970;
    public float $data_capacity = 30.0;
    public string $contract_holder = '';
    public string $actual_user = '';
    public string $network_pin = '';
    public string $contract_start_date = '';
    public string $notes = '';

    // 乗り換え元 (Previous) 情報
    public bool $is_transfer = false;
    public string $prev_carrier_name = '';
    public string $prev_plan_name = '';
    public int $prev_monthly_fee = 0;
    public float $prev_data_capacity = 0.0;
    public string $prev_start_date = '';

    // 端末代・キャッシュバック・手数料（実質収支シミュレーション用）
    public int $device_cost = 0;
    public int $cashback_amount = 0;
    public int $admin_fee = 3850;
    public int $device_sale_profit = 0;
    public ?int $custom_safe_period_days = null;

    // 暗証番号表示フラグ
    public bool $showPin = false;

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->contract_holder = $user->name;
        $this->actual_user = $user->name;
        $this->contract_start_date = Carbon::now()->format('Y-m-d');

        if ($this->from_line_id) {
            $prevLine = $user->lines()->find($this->from_line_id);
            if ($prevLine) {
                $this->is_transfer = true;
                $this->line_name = $prevLine->line_name;
                $this->phone_number = $prevLine->phone_number ?? '';
                $this->contract_holder = $prevLine->contract_holder;
                $this->actual_user = $prevLine->actual_user;
                $this->network_pin = $prevLine->network_pin ?? '';

                $this->prev_carrier_name = $prevLine->carrier_name;
                $this->prev_plan_name = $prevLine->plan_name ?? '';
                $this->prev_monthly_fee = $prevLine->monthly_fee;
                $this->prev_data_capacity = (float)$prevLine->data_capacity;
                $this->prev_start_date = $prevLine->contract_start_date ? $prevLine->contract_start_date->format('Y-m-d') : '';
            }
        }
    }

    public function updatedCarrierName(mixed $value): void
    {
        $carrier = Carrier::where('name', (string)$value)->first();
        if ($carrier) {
            $this->carrier_id = $carrier->id;
        } else {
            $this->carrier_id = null;
        }
    }

    public function selectDataCapacity(float $capacity): void
    {
        $this->data_capacity = $capacity;
    }

    public function togglePin(): void
    {
        $this->showPin = !$this->showPin;
    }

    public function getMonthlySavingProperty(): int
    {
        if ($this->prev_monthly_fee > 0 && $this->monthly_fee >= 0) {
            return $this->prev_monthly_fee - $this->monthly_fee;
        }
        return 0;
    }

    public function getAnnualSavingProperty(): int
    {
        return $this->monthlySaving * 12;
    }

    public function getDataDifferenceProperty(): float
    {
        if ($this->prev_data_capacity > 0) {
            return $this->data_capacity - $this->prev_data_capacity;
        }
        return 0;
    }

    public function getPrevUsagePeriodTextProperty(): string
    {
        if (!$this->prev_start_date) {
            return '';
        }
        $start = Carbon::parse($this->prev_start_date);
        $diff = $start->diff(Carbon::now());
        $years = $diff->y;
        $months = $diff->m;
        $parts = [];
        if ($years > 0) $parts[] = "{$years}年";
        if ($months > 0 || $years > 0) $parts[] = "{$months}ヶ月";
        if (empty($parts)) $parts[] = "{$diff->d}日";
        return implode('', $parts);
    }

    public function save(): void
    {
        $this->validate([
            'line_name' => ['required', 'string', 'max:255'],
            'carrier_name' => ['required', 'string', 'max:255'],
            'monthly_fee' => ['required', 'integer', 'min:0'],
            'data_capacity' => ['required', 'numeric', 'min:0'],
            'contract_holder' => ['required', 'string', 'max:255'],
            'actual_user' => ['required', 'string', 'max:255'],
            'contract_start_date' => ['required', 'date'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 既存回線の乗り換え実行の場合
        if ($this->from_line_id) {
            $line = $user->lines()->findOrFail($this->from_line_id);

            // 履歴にアーカイブ
            $usageMonths = $line->contract_start_date ? Carbon::parse($line->contract_start_date)->diffInMonths(Carbon::now()) : 0;
            $monthlySaving = $this->prev_monthly_fee - $this->monthly_fee;
            $annualSaving = $monthlySaving * 12;
            $totalNetSaving = $annualSaving + (int)$this->cashback_amount + (int)$this->device_sale_profit - (int)$this->device_cost - (int)$this->admin_fee;

            TransferHistory::create([
                'user_id' => $user->id,
                'line_id' => $line->id,
                'phone_number' => $this->phone_number ?: $line->phone_number,
                'line_name' => $this->line_name,
                'contract_holder' => $this->contract_holder,
                'actual_user' => $this->actual_user,
                'from_carrier_name' => $this->prev_carrier_name ?: $line->carrier_name,
                'from_plan_name' => $this->prev_plan_name ?: $line->plan_name,
                'from_monthly_fee' => $this->prev_monthly_fee ?: $line->monthly_fee,
                'from_data_capacity' => $this->prev_data_capacity ?: $line->data_capacity,
                'to_carrier_name' => $this->carrier_name,
                'to_plan_name' => $this->plan_name,
                'to_monthly_fee' => $this->monthly_fee,
                'to_data_capacity' => $this->data_capacity,
                'transfer_date' => $this->contract_start_date ?: Carbon::now(),
                'usage_period_text' => $line->usage_period_human,
                'usage_period_months' => $usageMonths,
                'monthly_saving' => $monthlySaving,
                'annual_saving' => $annualSaving,
                'device_cost' => (int)$this->device_cost,
                'cashback_amount' => (int)$this->cashback_amount,
                'admin_fee' => (int)$this->admin_fee,
                'device_sale_profit' => (int)$this->device_sale_profit,
                'total_net_saving' => $totalNetSaving,
                'notes' => $this->notes,
            ]);

            // 回線データを新契約情報に更新
            $line->update([
                'line_name' => $this->line_name,
                'phone_number' => $this->phone_number ?: null,
                'carrier_id' => $this->carrier_id,
                'carrier_name' => $this->carrier_name,
                'plan_name' => $this->plan_name ?: null,
                'monthly_fee' => $this->monthly_fee,
                'data_capacity' => $this->data_capacity,
                'contract_holder' => $this->contract_holder,
                'actual_user' => $this->actual_user,
                'network_pin' => $this->network_pin ?: null,
                'contract_start_date' => $this->contract_start_date,
                'custom_safe_period_days' => $this->custom_safe_period_days ?: null,
                'status' => 'active',
                'notes' => $this->notes ?: null,
            ]);

            session()->flash('status', "{$this->prev_carrier_name} から {$this->carrier_name} への乗り換え（MNP）を完了し、履歴に記録しました。");
        } else {
            // 新規回線の作成
            $line = Line::create([
                'user_id' => $user->id,
                'carrier_id' => $this->carrier_id,
                'line_name' => $this->line_name,
                'phone_number' => $this->phone_number ?: null,
                'contract_holder' => $this->contract_holder,
                'actual_user' => $this->actual_user,
                'network_pin' => $this->network_pin ?: null,
                'carrier_name' => $this->carrier_name,
                'plan_name' => $this->plan_name ?: null,
                'monthly_fee' => $this->monthly_fee,
                'data_capacity' => $this->data_capacity,
                'contract_start_date' => $this->contract_start_date,
                'custom_safe_period_days' => $this->custom_safe_period_days ?: null,
                'status' => 'active',
                'notes' => $this->notes ?: null,
            ]);

            // 乗り換え元情報が手入力されている場合は履歴にも登録
            if ($this->prev_carrier_name && $this->prev_monthly_fee > 0) {
                $monthlySaving = $this->prev_monthly_fee - $this->monthly_fee;
                $annualSaving = $monthlySaving * 12;
                $totalNetSaving = $annualSaving + (int)$this->cashback_amount + (int)$this->device_sale_profit - (int)$this->device_cost - (int)$this->admin_fee;

                TransferHistory::create([
                    'user_id' => $user->id,
                    'line_id' => $line->id,
                    'phone_number' => $this->phone_number,
                    'line_name' => $this->line_name,
                    'contract_holder' => $this->contract_holder,
                    'actual_user' => $this->actual_user,
                    'from_carrier_name' => $this->prev_carrier_name,
                    'from_plan_name' => $this->prev_plan_name,
                    'from_monthly_fee' => $this->prev_monthly_fee,
                    'from_data_capacity' => $this->prev_data_capacity,
                    'to_carrier_name' => $this->carrier_name,
                    'to_plan_name' => $this->plan_name,
                    'to_monthly_fee' => $this->monthly_fee,
                    'to_data_capacity' => $this->data_capacity,
                    'transfer_date' => $this->contract_start_date ?: Carbon::now(),
                    'usage_period_text' => $this->prev_usage_period_text ?: null,
                    'monthly_saving' => $monthlySaving,
                    'annual_saving' => $annualSaving,
                    'device_cost' => (int)$this->device_cost,
                    'cashback_amount' => (int)$this->cashback_amount,
                    'admin_fee' => (int)$this->admin_fee,
                    'device_sale_profit' => (int)$this->device_sale_profit,
                    'total_net_saving' => $totalNetSaving,
                    'notes' => $this->notes,
                ]);
            }

            session()->flash('status', '新しい回線契約を登録しました。');
        }

        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $carriers = Carrier::where('is_active', true)->orderBy('display_order')->get();
        $userLines = $user->lines()->where('status', 'active')->get();

        // 差額計算
        $monthlyDiff = $this->prev_monthly_fee > 0 ? ($this->prev_monthly_fee - $this->monthly_fee) : 0;
        $annualDiff = $monthlyDiff * 12;
        $totalNetDiff = $annualDiff + (int)$this->cashback_amount + (int)$this->device_sale_profit - (int)$this->device_cost - (int)$this->admin_fee;
        $dataDiff = $this->prev_data_capacity > 0 ? ($this->data_capacity - $this->prev_data_capacity) : 0;

        // 選択されたキャリアの安全日数
        $selectedCarrier = $carriers->firstWhere('name', $this->carrier_name);
        $recommendedSafeDays = $selectedCarrier?->safe_period_days ?? 180;

        return view('livewire.line-create-comparison', [
            'carriers' => $carriers,
            'userLines' => $userLines,
            'monthlyDiff' => $monthlyDiff,
            'annualDiff' => $annualDiff,
            'totalNetDiff' => $totalNetDiff,
            'dataDiff' => $dataDiff,
            'recommendedSafeDays' => $recommendedSafeDays,
        ]);
    }
}
