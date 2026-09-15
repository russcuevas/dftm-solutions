<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableDailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumable_item_id',
        'log_date',
        'in_qty',
        'out_qty',
        'reference_no',
        'remarks',
        'user_id',
    ];

    protected $casts = [
        'log_date' => 'date:Y-m-d',
        'in_qty' => 'integer',
        'out_qty' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(ConsumableItem::class, 'consumable_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
