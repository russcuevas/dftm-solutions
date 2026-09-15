<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableMonthlyStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumable_item_id',
        'year',
        'month',
        'beginning_stock',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'beginning_stock' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(ConsumableItem::class, 'consumable_item_id');
    }
}
