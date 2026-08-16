<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'safe_period_days',
        'brand_color_bg',
        'brand_color_text',
        'brand_color_border',
        'badge_color',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'safe_period_days' => 'integer',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(Line::class);
    }
}
