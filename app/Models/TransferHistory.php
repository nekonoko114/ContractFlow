<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'line_id',
        'phone_number',
        'line_name',
        'contract_holder',
        'actual_user',
        'from_carrier_name',
        'from_plan_name',
        'from_monthly_fee',
        'from_data_capacity',
        'to_carrier_name',
        'to_plan_name',
        'to_monthly_fee',
        'to_data_capacity',
        'transfer_date',
        'usage_period_text',
        'usage_period_months',
        'monthly_saving',
        'annual_saving',
        'device_cost',
        'cashback_amount',
        'admin_fee',
        'device_sale_profit',
        'total_net_saving',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
            'from_monthly_fee' => 'integer',
            'from_data_capacity' => 'decimal:2',
            'to_monthly_fee' => 'integer',
            'to_data_capacity' => 'decimal:2',
            'monthly_saving' => 'integer',
            'annual_saving' => 'integer',
            'device_cost' => 'integer',
            'cashback_amount' => 'integer',
            'admin_fee' => 'integer',
            'device_sale_profit' => 'integer',
            'total_net_saving' => 'integer',
            'usage_period_months' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }
}
