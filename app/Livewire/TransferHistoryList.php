<?php

namespace App\Livewire;

use App\Models\Carrier;
use App\Models\TransferHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('乗り換え履歴 - ContractFlow')]
class TransferHistoryList extends Component
{
    // 新規履歴手動登録モーダル
    public bool $isCreating = false;
    public string $new_phone_number = '';
    public string $new_line_name = 'メイン回線';
    public string $new_contract_holder = '';
    public string $new_actual_user = '';
    public string $new_from_carrier_name = 'NTTドコモ';
    public string $new_from_plan_name = '';
    public int $new_from_monthly_fee = 7315;
    public float $new_from_data_capacity = 60.0;
    public string $new_to_carrier_name = 'ahamo';
    public string $new_to_plan_name = '';
    public int $new_to_monthly_fee = 2970;
    public float $new_to_data_capacity = 30.0;
    public string $new_transfer_date = '';
    public string $new_usage_period_text = '2年0ヶ月';
    public string $new_notes = '';

    // 実質収支プロパティ
    public int $new_device_cost = 0;
    public int $new_cashback_amount = 0;
    public int $new_admin_fee = 3850;
    public int $new_device_sale_profit = 0;

    // 検索プロパティ
    public string $search = '';

    // 削除確認モーダル
    public bool $confirmingDelete = false;
    public ?int $deletingHistoryId = null;

    public ?string $toastMessage = null;

    public function resetFilters(): void
    {
        $this->search = '';
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $histories = $user->transferHistories()->orderByDesc('transfer_date')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contractflow_transfer_histories_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($histories) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'ID',
                '乗り換え日',
                '回線名',
                '電話番号',
                '乗り換え元キャリア',
                '乗り換え前月額(円)',
                '乗り換え先キャリア',
                '乗り換え後月額(円)',
                '月額節約額(円)',
                '年間月額削減額(円)',
                '端末購入費(円)',
                'CB還元額(円)',
                '事務手数料(円)',
                '端末売却益(円)',
                'トータル実質収支(円)',
                '名義人',
                '使用者',
                'メモ',
            ]);

            foreach ($histories as $h) {
                fputcsv($handle, [
                    $h->id,
                    $h->transfer_date ? $h->transfer_date->format('Y-m-d') : '',
                    $h->line_name,
                    $h->phone_number ?? '',
                    $h->from_carrier_name,
                    $h->from_monthly_fee,
                    $h->to_carrier_name,
                    $h->to_monthly_fee,
                    $h->monthly_saving,
                    $h->annual_saving,
                    $h->device_cost ?? 0,
                    $h->cashback_amount ?? 0,
                    $h->admin_fee ?? 0,
                    $h->device_sale_profit ?? 0,
                    $h->total_net_saving ?? $h->annual_saving,
                    $h->contract_holder,
                    $h->actual_user,
                    $h->notes ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function mount(): void
    {
        $this->new_transfer_date = Carbon::now()->format('Y-m-d');
        $this->new_contract_holder = Auth::user()->name;
        $this->new_actual_user = Auth::user()->name;
    }

    public function openCreateModal(): void
    {
        $this->isCreating = true;
    }

    public function closeCreateModal(): void
    {
        $this->isCreating = false;
    }

    public function createHistory(): void
    {
        $this->validate([
            'new_from_carrier_name' => ['required', 'string'],
            'new_to_carrier_name' => ['required', 'string'],
            'new_from_monthly_fee' => ['required', 'integer', 'min:0'],
            'new_to_monthly_fee' => ['required', 'integer', 'min:0'],
            'new_transfer_date' => ['required', 'date'],
        ]);

        $monthlySaving = $this->new_from_monthly_fee - $this->new_to_monthly_fee;
        $annualSaving = $monthlySaving * 12;
        $totalNetSaving = $annualSaving + (int)$this->new_cashback_amount + (int)$this->new_device_sale_profit - (int)$this->new_device_cost - (int)$this->new_admin_fee;

        TransferHistory::create([
            'user_id' => Auth::id(),
            'phone_number' => $this->new_phone_number ?: null,
            'line_name' => $this->new_line_name ?: 'メイン回線',
            'contract_holder' => $this->new_contract_holder ?: Auth::user()->name,
            'actual_user' => $this->new_actual_user ?: Auth::user()->name,
            'from_carrier_name' => $this->new_from_carrier_name,
            'from_plan_name' => $this->new_from_plan_name ?: null,
            'from_monthly_fee' => $this->new_from_monthly_fee,
            'from_data_capacity' => $this->new_from_data_capacity,
            'to_carrier_name' => $this->new_to_carrier_name,
            'to_plan_name' => $this->new_to_plan_name ?: null,
            'to_monthly_fee' => $this->new_to_monthly_fee,
            'to_data_capacity' => $this->new_to_data_capacity,
            'transfer_date' => $this->new_transfer_date,
            'usage_period_text' => $this->new_usage_period_text ?: null,
            'monthly_saving' => $monthlySaving,
            'annual_saving' => $annualSaving,
            'device_cost' => (int)$this->new_device_cost,
            'cashback_amount' => (int)$this->new_cashback_amount,
            'admin_fee' => (int)$this->new_admin_fee,
            'device_sale_profit' => (int)$this->new_device_sale_profit,
            'total_net_saving' => $totalNetSaving,
            'notes' => $this->new_notes ?: null,
        ]);

        $this->isCreating = false;
        $this->toastMessage = '過去の乗り換え履歴を追加しました。';
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingHistoryId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteHistory(): void
    {
        if ($this->deletingHistoryId) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $history = $user->transferHistories()->findOrFail($this->deletingHistoryId);
            $history->delete();
            $this->confirmingDelete = false;
            $this->deletingHistoryId = null;
            $this->toastMessage = '履歴を削除しました。';
        }
    }

    public function clearToast(): void
    {
        $this->toastMessage = null;
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $allHistories = $user->transferHistories()->get();
        $carriers = Carrier::where('is_active', true)->orderBy('display_order')->get();

        $query = $user->transferHistories();
        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('line_name', 'like', $term)
                  ->orWhere('phone_number', 'like', $term)
                  ->orWhere('from_carrier_name', 'like', $term)
                  ->orWhere('to_carrier_name', 'like', $term)
                  ->orWhere('from_plan_name', 'like', $term)
                  ->orWhere('to_plan_name', 'like', $term)
                  ->orWhere('contract_holder', 'like', $term)
                  ->orWhere('actual_user', 'like', $term)
                  ->orWhere('notes', 'like', $term);
            });
        }

        $histories = $query->orderByDesc('transfer_date')->get();

        $totalAnnualSaving = $allHistories->sum('annual_saving');
        $totalMonthlySaving = $allHistories->sum('monthly_saving');
        $historyCount = $allHistories->count();
        $avgMonthlySaving = $historyCount > 0 ? (int)($totalMonthlySaving / $historyCount) : 0;

        // 電話番号別の乗り換え回数・節約額集計
        $phoneCounts = [];
        foreach ($allHistories->groupBy('phone_number') as $pNumber => $items) {
            if (!empty($pNumber)) {
                $phoneCounts[] = [
                    'phone_number' => $pNumber,
                    'line_name' => $items->first()->line_name,
                    'count' => $items->count(),
                    'annual_saving' => $items->sum('annual_saving'),
                ];
            }
        }
        usort($phoneCounts, fn($a, $b) => $b['count'] <=> $a['count']);

        return view('livewire.transfer-history-list', [
            'histories' => $histories,
            'totalHistoriesCount' => $allHistories->count(),
            'carriers' => $carriers,
            'phoneCounts' => $phoneCounts,
            'totalAnnualSaving' => $totalAnnualSaving,
            'historyCount' => $historyCount,
            'avgMonthlySaving' => $avgMonthlySaving,
        ]);
    }
}
