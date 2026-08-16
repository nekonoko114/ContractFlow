<?php

namespace App\Livewire;

use App\Models\Carrier;
use App\Models\Line;
use App\Models\TransferHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('ダッシュボード - ContractFlow')]
class Dashboard extends Component
{
    use WithFileUploads;

    // 表示モード ('table' または 'grid')
    public string $viewMode = 'table';

    // 暗証番号表示用ステート (line_id => bool)
    public array $showPins = [];

    // CSV インポート
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $csvFile = null;
    public bool $isImporting = false;

    // 編集モーダル用プロパティ
    public bool $isEditing = false;
    public ?int $editingLineId = null;
    public string $edit_line_name = '';
    public string $edit_phone_number = '';
    public string $edit_contract_holder = '';
    public string $edit_actual_user = '';
    public string $edit_network_pin = '';
    public string $edit_carrier_name = '';
    public ?int $edit_carrier_id = null;
    public string $edit_plan_name = '';
    public int $edit_monthly_fee = 0;
    public float $edit_data_capacity = 0;
    public string $edit_contract_start_date = '';
    public ?int $edit_custom_safe_period_days = null;
    public string $edit_status = 'active';
    public ?string $edit_mnp_reservation_number = '';
    public ?string $edit_mnp_reservation_expire_date = '';
    public string $edit_notes = '';

    // 検索・フィルター用プロパティ
    public string $search = '';
    public string $carrierFilter = '';
    public string $statusFilter = '';
    public string $safeFilter = '';
    public string $sortBy = 'id';
    public string $sortDirection = 'asc';

    // 回線ごとの乗り換え履歴モーダル
    public bool $viewingLineHistory = false;
    public ?int $selectedLineId = null;

    // 削除確認モーダル
    public bool $confirmingDelete = false;
    public ?int $deletingLineId = null;

    // トースト通知メッセージ
    public ?string $toastMessage = null;

    public function openImportModal(): void
    {
        $this->isImporting = true;
        $this->csvFile = null;
    }

    public function closeImportModal(): void
    {
        $this->isImporting = false;
        $this->csvFile = null;
    }

    public function exportCsv(): StreamedResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $lines = $user->lines()->orderBy('id')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contractflow_lines_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($lines) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($handle, [
                'ID',
                '回線識別名',
                '電話番号',
                '携帯会社名',
                '料金プラン名',
                '月額料金(円)',
                '通信容量(GB)',
                '契約名義人',
                '使用者',
                '契約開始日',
                '利用日数',
                '目標安全維持日数',
                '安全状態',
                'ステータス',
                '乗り換え回数',
                'MNP予約番号',
                'MNP有効期限',
                'メモ',
            ]);

            foreach ($lines as $l) {
                fputcsv($handle, [
                    $l->id,
                    $l->line_name,
                    $l->phone_number ?? '',
                    $l->carrier_name,
                    $l->plan_name ?? '',
                    $l->monthly_fee,
                    $l->data_capacity,
                    $l->contract_holder,
                    $l->actual_user,
                    $l->contract_start_date ? $l->contract_start_date->format('Y-m-d') : '',
                    $l->usage_days,
                    $l->target_safe_period_days,
                    $l->safe_status === 'safe' ? '安全達成(転出OK)' : ($l->safe_status === 'caution' ? 'まもなく安全' : '短期利用中'),
                    $l->status_label,
                    $l->transfer_count,
                    $l->mnp_reservation_number ?? '',
                    $l->mnp_reservation_expire_date ? $l->mnp_reservation_expire_date->format('Y-m-d') : '',
                    $l->notes ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(): void
    {
        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $path = $this->csvFile->getRealPath();
        $content = file_get_contents($path);
        
        // UTF-8 BOM の除去
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);
        
        // SJIS から UTF-8 への自動変換
        if (!mb_detect_encoding($content, 'UTF-8', true)) {
            $content = mb_convert_encoding($content, 'UTF-8', 'SJIS-win, CP932, EUC-JP, auto');
        }

        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));
        $importedCount = 0;

        foreach ($lines as $i => $lineStr) {
            if ($i === 0 || trim($lineStr) === '') continue; // ヘッダーまたは空行スキップ
            $row = str_getcsv($lineStr);
            if (count($row) < 5) continue;

            $lineName = $row[1] ?? '新規回線';
            $phoneNumber = $row[2] ?? null;
            $carrierName = $row[3] ?? 'その他';
            $planName = $row[4] ?? null;
            $monthlyFee = isset($row[5]) && is_numeric($row[5]) ? (int)$row[5] : 3000;
            $dataCapacity = isset($row[6]) && is_numeric($row[6]) ? (float)$row[6] : 20.0;
            $contractHolder = $row[7] ?? $user->name;
            $actualUser = $row[8] ?? $user->name;
            $startDate = !empty($row[9]) ? $row[9] : date('Y-m-d');
            $customSafeDays = isset($row[11]) && is_numeric($row[11]) ? (int)$row[11] : null;
            $notes = $row[17] ?? null;

            $carrier = Carrier::where('name', $carrierName)->first();

            $user->lines()->create([
                'carrier_id' => $carrier?->id,
                'carrier_name' => $carrierName,
                'line_name' => $lineName,
                'phone_number' => $phoneNumber,
                'plan_name' => $planName,
                'monthly_fee' => $monthlyFee,
                'data_capacity' => $dataCapacity,
                'contract_holder' => $contractHolder,
                'actual_user' => $actualUser,
                'contract_start_date' => $startDate,
                'custom_safe_period_days' => $customSafeDays,
                'status' => 'active',
                'notes' => $notes,
            ]);
            $importedCount++;
        }

        $this->isImporting = false;
        $this->csvFile = null;
        $this->toastMessage = "CSVから {$importedCount} 件の回線情報をインポートしました。";
    }

    public function openLineHistoryModal(int $lineId): void
    {
        $this->selectedLineId = $lineId;
        $this->viewingLineHistory = true;
    }

    public function closeLineHistoryModal(): void
    {
        $this->viewingLineHistory = false;
        $this->selectedLineId = null;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->carrierFilter = '';
        $this->statusFilter = '';
        $this->safeFilter = '';
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function togglePin(int $lineId): void
    {
        $this->showPins[$lineId] = !($this->showPins[$lineId] ?? false);
    }

    public function openEditModal(int $lineId): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $line = $user->lines()->findOrFail($lineId);

        $this->editingLineId = $line->id;
        $this->edit_line_name = $line->line_name;
        $this->edit_phone_number = $line->phone_number ?? '';
        $this->edit_contract_holder = $line->contract_holder;
        $this->edit_actual_user = $line->actual_user;
        $this->edit_network_pin = $line->network_pin ?? '';
        $this->edit_carrier_name = $line->carrier_name;
        $this->edit_carrier_id = $line->carrier_id;
        $this->edit_plan_name = $line->plan_name ?? '';
        $this->edit_monthly_fee = $line->monthly_fee;
        $this->edit_data_capacity = (float)$line->data_capacity;
        $this->edit_contract_start_date = $line->contract_start_date ? $line->contract_start_date->format('Y-m-d') : '';
        $this->edit_custom_safe_period_days = $line->custom_safe_period_days;
        $this->edit_status = $line->status;
        $this->edit_mnp_reservation_number = $line->mnp_reservation_number ?? '';
        $this->edit_mnp_reservation_expire_date = $line->mnp_reservation_expire_date ? $line->mnp_reservation_expire_date->format('Y-m-d') : '';
        $this->edit_notes = $line->notes ?? '';

        $this->isEditing = true;
    }

    public function closeEditModal(): void
    {
        $this->isEditing = false;
        $this->editingLineId = null;
    }

    public function updateLine(): void
    {
        $this->validate([
            'edit_contract_holder' => ['required', 'string', 'max:255'],
            'edit_actual_user' => ['required', 'string', 'max:255'],
            'edit_carrier_name' => ['required', 'string', 'max:255'],
            'edit_monthly_fee' => ['required', 'integer', 'min:0'],
            'edit_data_capacity' => ['required', 'numeric', 'min:0'],
            'edit_contract_start_date' => ['required', 'date'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $line = $user->lines()->findOrFail($this->editingLineId);

        $carrier = Carrier::where('name', $this->edit_carrier_name)->first();

        $line->update([
            'line_name' => $this->edit_line_name ?: 'メイン回線',
            'phone_number' => $this->edit_phone_number ?: null,
            'contract_holder' => $this->edit_contract_holder,
            'actual_user' => $this->edit_actual_user,
            'network_pin' => $this->edit_network_pin ?: null,
            'carrier_name' => $this->edit_carrier_name,
            'carrier_id' => $carrier?->id ?? $this->edit_carrier_id,
            'plan_name' => $this->edit_plan_name ?: null,
            'monthly_fee' => $this->edit_monthly_fee,
            'data_capacity' => $this->edit_data_capacity,
            'contract_start_date' => $this->edit_contract_start_date,
            'custom_safe_period_days' => $this->edit_custom_safe_period_days ?: null,
            'status' => $this->edit_status,
            'mnp_reservation_number' => $this->edit_mnp_reservation_number ?: null,
            'mnp_reservation_expire_date' => $this->edit_mnp_reservation_expire_date ?: null,
            'notes' => $this->edit_notes ?: null,
        ]);

        $this->isEditing = false;
        $this->toastMessage = '回線情報を更新しました。';
    }

    public function confirmDelete(int $lineId): void
    {
        $this->deletingLineId = $lineId;
        $this->confirmingDelete = true;
    }

    public function deleteLine(): void
    {
        if ($this->deletingLineId) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $line = $user->lines()->findOrFail($this->deletingLineId);
            $line->delete();
            $this->confirmingDelete = false;
            $this->deletingLineId = null;
            $this->toastMessage = '回線を削除しました。';
        }
    }

    public function clearToast(): void
    {
        $this->toastMessage = null;
    }

    public function render(): \Illuminate\View\View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $allLines = $user->lines()->get();
        $carriers = Carrier::where('is_active', true)->orderBy('display_order')->get();
        $allHistories = TransferHistory::where('user_id', $user->id)->get();

        // 絞り込みクエリの構築
        $query = $user->lines();

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('line_name', 'like', $term)
                  ->orWhere('phone_number', 'like', $term)
                  ->orWhere('carrier_name', 'like', $term)
                  ->orWhere('plan_name', 'like', $term)
                  ->orWhere('contract_holder', 'like', $term)
                  ->orWhere('actual_user', 'like', $term)
                  ->orWhere('notes', 'like', $term);
            });
        }

        if ($this->carrierFilter !== '') {
            $query->where('carrier_name', $this->carrierFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        // ソート
        $validSorts = ['id', 'line_name', 'carrier_name', 'monthly_fee', 'data_capacity', 'contract_start_date'];
        $sortColumn = in_array($this->sortBy, $validSorts) ? $this->sortBy : 'id';
        $lines = $query->orderBy($sortColumn, $this->sortDirection === 'desc' ? 'desc' : 'asc')->get();

        // 安全ステータスフィルター
        if ($this->safeFilter !== '') {
            $lines = $lines->filter(fn($l) => $l->safe_status === $this->safeFilter)->values();
        }

        // 全体の統計情報
        $totalMonthlyFee = (int)$allLines->where('status', '!=', 'cancelled')->sum('monthly_fee');
        $totalDataCapacity = (float)$allLines->where('status', '!=', 'cancelled')->sum('data_capacity');
        $activeLinesCount = $allLines->where('status', 'active')->count();
        $totalSavings = (int)$allHistories->sum('annual_saving');

        // キャリア別および電話番号別の統計集計
        $carrierStats = $this->buildCarrierStats($carriers, $allLines, $allHistories);
        $phoneStats = $this->buildPhoneStats($allLines, $allHistories);

        // 選択された回線の履歴
        $selectedLine = null;
        $selectedLineHistories = collect();
        if ($this->selectedLineId) {
            $selectedLine = $user->lines()->find($this->selectedLineId);
            if ($selectedLine) {
                $selectedLineHistories = TransferHistory::where('user_id', $user->id)
                    ->where(function ($q) use ($selectedLine) {
                        $q->where('line_id', $selectedLine->id);
                        if (!empty($selectedLine->phone_number)) {
                            $rawPhone = $selectedLine->phone_number;
                            $norm = preg_replace('/[^0-9]/', '', $rawPhone);
                            $q->orWhere('phone_number', $rawPhone);
                            if (!empty($norm)) {
                                $q->orWhereRaw("REPLACE(REPLACE(phone_number, '-', ''), ' ', '') = ?", [$norm]);
                            }
                        }
                    })
                    ->orderByDesc('transfer_date')
                    ->get();
            }
        }

        return view('livewire.dashboard', [
            'lines' => $lines,
            'totalLinesCount' => $allLines->count(),
            'carriers' => $carriers,
            'carrierStats' => $carrierStats,
            'phoneStats' => $phoneStats,
            'selectedLine' => $selectedLine,
            'selectedLineHistories' => $selectedLineHistories,
            'totalMonthlyFee' => $totalMonthlyFee,
            'totalDataCapacity' => $totalDataCapacity,
            'activeLinesCount' => $activeLinesCount,
            'totalSavings' => $totalSavings,
        ]);
    }

    /**
     * キャリア別の契約・乗り換え実績カウント集計
     *
     * @param Collection<int, Carrier> $carriers
     * @param Collection<int, Line> $allLines
     * @param Collection<int, TransferHistory> $allHistories
     * @return array<string, array{name: string, active_count: int, to_count: int, from_count: int, total_count: int}>
     */
    private function buildCarrierStats(Collection $carriers, Collection $allLines, Collection $allHistories): array
    {
        $carrierStats = [];
        $allCarrierNames = array_unique(array_merge(
            $carriers->pluck('name')->toArray(),
            $allLines->pluck('carrier_name')->toArray(),
            $allHistories->pluck('from_carrier_name')->toArray(),
            $allHistories->pluck('to_carrier_name')->toArray()
        ));

        foreach ($allCarrierNames as $cName) {
            $activeCount = $allLines->where('carrier_name', $cName)->where('status', 'active')->count();
            $toCount = $allHistories->where('to_carrier_name', $cName)->count();
            $fromCount = $allHistories->where('from_carrier_name', $cName)->count();
            $totalCount = $toCount + $fromCount + $activeCount;

            if ($totalCount > 0) {
                $carrierStats[$cName] = [
                    'name' => $cName,
                    'active_count' => $activeCount,
                    'to_count' => $toCount,
                    'from_count' => $fromCount,
                    'total_count' => $totalCount,
                ];
            }
        }

        uasort($carrierStats, fn($a, $b) => $b['total_count'] <=> $a['total_count']);
        return $carrierStats;
    }

    /**
     * 電話番号別の乗り換え実績カウント集計
     *
     * @param Collection<int, Line> $allLines
     * @param Collection<int, TransferHistory> $allHistories
     * @return array<int, array{phone_number: string, line_name: string, current_carrier: string, transfer_count: int, carrier_flow: array<int, string>, annual_savings: int, line_id: int|null}>
     */
    private function buildPhoneStats(Collection $allLines, Collection $allHistories): array
    {
        $phoneStats = [];
        $uniquePhones = array_unique(array_filter(array_merge(
            $allLines->pluck('phone_number')->toArray(),
            $allHistories->pluck('phone_number')->toArray()
        )));

        foreach ($uniquePhones as $phone) {
            $matchingHistories = $allHistories->filter(function ($h) use ($phone) {
                return $h->phone_number === $phone || 
                       (!empty($phone) && preg_replace('/[^0-9]/', '', $h->phone_number) === preg_replace('/[^0-9]/', '', $phone));
            })->sortBy('transfer_date');

            $currentLine = $allLines->first(function ($l) use ($phone) {
                return $l->phone_number === $phone || 
                       (!empty($phone) && preg_replace('/[^0-9]/', '', $l->phone_number) === preg_replace('/[^0-9]/', '', $phone));
            });

            // キャリア遷移の組み立て
            $flow = [];
            foreach ($matchingHistories as $h) {
                if (empty($flow)) {
                    $flow[] = $h->from_carrier_name;
                }
                $flow[] = $h->to_carrier_name;
            }
            if ($currentLine && !empty($flow) && end($flow) !== $currentLine->carrier_name) {
                $flow[] = $currentLine->carrier_name;
            } elseif ($currentLine && empty($flow)) {
                $flow[] = $currentLine->carrier_name;
            }

            $phoneStats[] = [
                'phone_number' => $phone,
                'line_name' => $currentLine ? $currentLine->line_name : ($matchingHistories->last()?->line_name ?? '回線'),
                'current_carrier' => $currentLine ? $currentLine->carrier_name : ($matchingHistories->last()?->to_carrier_name ?? '-'),
                'transfer_count' => $matchingHistories->count(),
                'carrier_flow' => array_values(array_unique($flow)),
                'annual_savings' => $matchingHistories->sum('annual_saving'),
                'line_id' => $currentLine?->id,
            ];
        }

        // 検索キーワード・フィルターの適用
        if (trim($this->search) !== '') {
            $sTerm = mb_strtolower(trim($this->search));
            $phoneStats = array_filter($phoneStats, function ($stat) use ($sTerm) {
                return str_contains(mb_strtolower($stat['phone_number']), $sTerm) ||
                       str_contains(mb_strtolower($stat['line_name']), $sTerm) ||
                       str_contains(mb_strtolower($stat['current_carrier']), $sTerm) ||
                       collect($stat['carrier_flow'])->contains(fn($c) => str_contains(mb_strtolower($c), $sTerm));
            });
        }

        if ($this->carrierFilter !== '') {
            $cFilter = $this->carrierFilter;
            $phoneStats = array_filter($phoneStats, function ($stat) use ($cFilter) {
                return $stat['current_carrier'] === $cFilter || in_array($cFilter, $stat['carrier_flow']);
            });
        }

        usort($phoneStats, fn($a, $b) => $b['transfer_count'] <=> $a['transfer_count']);
        return $phoneStats;
    }
}
