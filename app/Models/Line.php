<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Line extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'carrier_id',
        'line_name',
        'phone_number',
        'contract_holder',
        'actual_user',
        'network_pin',
        'carrier_name',
        'plan_name',
        'monthly_fee',
        'data_capacity',
        'contract_start_date',
        'status',
        'custom_safe_period_days',
        'mnp_reservation_number',
        'mnp_reservation_expire_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'network_pin' => 'encrypted',
            'contract_start_date' => 'date',
            'mnp_reservation_expire_date' => 'date',
            'monthly_fee' => 'integer',
            'data_capacity' => 'decimal:2',
            'custom_safe_period_days' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function transferHistories(): HasMany
    {
        return $this->hasMany(TransferHistory::class)->orderByDesc('transfer_date');
    }

    /**
     * 利用期間の人間向け表記 (例: "1年3ヶ月", "6ヶ月", "15日")
     */
    protected function usagePeriodHuman(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->contract_start_date) {
                    return '未設定';
                }

                $start = Carbon::parse($this->contract_start_date)->startOfDay();
                $now = Carbon::now()->startOfDay();

                if ($start->isFuture()) {
                    return '契約開始前';
                }

                $diff = $start->diff($now);
                $years = $diff->y;
                $months = $diff->m;
                $days = $diff->d;

                $parts = [];
                if ($years > 0) {
                    $parts[] = "{$years}年";
                }
                if ($months > 0 || $years > 0) {
                    $parts[] = "{$months}ヶ月";
                }
                if (empty($parts)) {
                    $parts[] = "{$days}日";
                }

                $totalDays = $start->diffInDays($now);
                return implode('', $parts) . " ({$totalDays}日)";
            }
        );
    }

    /**
     * 利用月数 (概算)
     */
    protected function usageMonths(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->contract_start_date) {
                    return 0;
                }
                return Carbon::parse($this->contract_start_date)->diffInMonths(Carbon::now());
            }
        );
    }

    /**
     * ステータス日本語表記
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'active' => '契約中',
                    'reserved' => 'MNP予約中',
                    'transferred' => '乗り換え済',
                    'cancelled' => '解約済',
                    default => '契約中',
                };
            }
        );
    }

    /**
     * ステータスバッジのスタイル
     */
    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'reserved' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'transferred' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                };
            }
        );
    }

    /**
     * 数字のみの正規化された電話番号
     */
    protected function normalizedPhoneNumber(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->phone_number)) {
                    return '';
                }
                return preg_replace('/[^0-9]/', '', $this->phone_number);
            }
        );
    }

    /**
     * この電話番号での乗り換え回数（電話番号別の累計カウント）
     */
    protected function transferCount(): Attribute
    {
        return Attribute::make(
            get: function () {
                $count = 0;
                $user = $this->user_id ? $this->user : null;
                $userId = $this->user_id;

                if (!empty($this->phone_number)) {
                    $rawPhone = $this->phone_number;
                    $normalized = preg_replace('/[^0-9]/', '', $rawPhone);

                    // 電話番号（完全一致または正規化一致）で履歴をカウント
                    $count = TransferHistory::where('user_id', $userId)
                        ->where(function ($q) use ($rawPhone, $normalized) {
                            $q->where('phone_number', $rawPhone);
                            if (!empty($normalized)) {
                                $q->orWhereRaw("REPLACE(REPLACE(phone_number, '-', ''), ' ', '') = ?", [$normalized]);
                            }
                        })
                        ->count();

                    // line_id に紐づくものも含め重複排除
                    if ($count === 0 && $this->id) {
                        $count = TransferHistory::where('user_id', $userId)
                            ->where('line_id', $this->id)
                            ->count();
                    }
                } elseif ($this->id) {
                    $count = TransferHistory::where('user_id', $userId)
                        ->where('line_id', $this->id)
                        ->count();
                }

                return $count;
            }
        );
    }

    /**
     * 目標安全維持日数（キャリア設定または個別設定）
     */
    protected function targetSafePeriodDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!empty($this->custom_safe_period_days)) {
                    return $this->custom_safe_period_days;
                }
                if ($this->carrier && $this->carrier->safe_period_days) {
                    return $this->carrier->safe_period_days;
                }
                // キャリア名からの推測
                $cName = $this->carrier_name;
                if (str_contains($cName, 'au') || str_contains($cName, 'UQ')) {
                    return 211;
                }
                if (str_contains($cName, 'mineo') || str_contains($cName, 'IIJ') || str_contains($cName, '日本通信') || str_contains($cName, 'イオン') || str_contains($cName, 'NURO')) {
                    return 90;
                }
                return 180;
            }
        );
    }

    /**
     * 契約開始日からの経過日数
     */
    protected function usageDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->contract_start_date) {
                    return 0;
                }
                return max(0, (int)Carbon::parse($this->contract_start_date)->diffInDays(Carbon::now()));
            }
        );
    }

    /**
     * 安全期間達成までの残り日数 (0なら達成)
     */
    protected function safePeriodRemainingDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                $target = $this->target_safe_period_days;
                $used = $this->usage_days;
                return max(0, $target - $used);
            }
        );
    }

    /**
     * 安全期間の進捗率 (0〜100%)
     */
    protected function safePeriodProgress(): Attribute
    {
        return Attribute::make(
            get: function () {
                $target = $this->target_safe_period_days;
                if ($target <= 0) return 100;
                $used = $this->usage_days;
                return min(100, (int)round(($used / $target) * 100));
            }
        );
    }

    /**
     * 短期解約防止判定ステータス ('safe' | 'caution' | 'danger')
     */
    protected function safeStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->safe_period_remaining_days <= 0) {
                    return 'safe'; // 安全達成（転出OK）
                }
                if ($this->safe_period_progress >= 75 || $this->safe_period_remaining_days <= 30) {
                    return 'caution'; // まもなく安全（残りわずか）
                }
                return 'danger'; // 短期利用中（BL警戒）
            }
        );
    }

    /**
     * MNP予約番号の有効期限残り日数 (マイナスは期限切れ)
     */
    protected function mnpDaysRemaining(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->mnp_reservation_expire_date) {
                    return null;
                }
                return (int)Carbon::now()->startOfDay()->diffInDays(Carbon::parse($this->mnp_reservation_expire_date)->startOfDay(), false);
            }
        );
    }

    /**
     * キャリアブランドカラーバッジクラス
     */
    protected function brandBadgeClass(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->carrier && $this->carrier->brand_color_bg) {
                    return "{$this->carrier->brand_color_bg} {$this->carrier->brand_color_text} {$this->carrier->brand_color_border}";
                }
                $c = $this->carrier_name;
                return match (true) {
                    str_contains($c, 'ドコモ') => 'bg-red-50 text-red-700 border-red-200',
                    str_contains($c, 'ahamo') => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    str_contains($c, 'au') => 'bg-orange-50 text-orange-700 border-orange-200',
                    str_contains($c, 'UQ') => 'bg-sky-50 text-sky-700 border-sky-200',
                    str_contains($c, 'povo') => 'bg-amber-50 text-amber-800 border-amber-200',
                    str_contains($c, 'SoftBank') => 'bg-slate-100 text-slate-800 border-slate-300',
                    str_contains($c, 'Y!mobile') || str_contains($c, 'ワイモバ') => 'bg-rose-50 text-rose-700 border-rose-200',
                    str_contains($c, 'LINEMO') => 'bg-lime-50 text-lime-700 border-lime-200',
                    str_contains($c, '楽天') => 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
                    default => 'bg-[#f2f3ff] text-[#3525cd] border-[#dae2fd]',
                };
            }
        );
    }
}
