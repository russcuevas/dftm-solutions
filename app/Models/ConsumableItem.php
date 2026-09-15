<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'unit',
        'cost',
        'min_stock',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
        'min_stock' => 'integer',
        'sort_order' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ConsumableCategory::class, 'category_id');
    }

    public function monthlyStocks()
    {
        return $this->hasMany(ConsumableMonthlyStock::class, 'consumable_item_id');
    }

    public function dailyLogs()
    {
        return $this->hasMany(ConsumableDailyLog::class, 'consumable_item_id');
    }

    public function getBeginningStockForMonth($year, $month)
    {
        $stock = $this->monthlyStocks()->where('year', $year)->where('month', $month)->first();
        return $stock ? $stock->beginning_stock : 0;
    }
}
