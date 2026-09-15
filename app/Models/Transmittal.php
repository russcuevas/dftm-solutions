<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transmittal extends Model
{
    use HasFactory;

    protected $fillable = [
        'transmittal_no',
        'company_name',
        'date_received',
        'brand',
        'model',
        'status',
        'total_quantity',
        'notes',
        'encoded_by',
    ];

    protected $casts = [
        'date_received' => 'date',
        'total_quantity' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'transmittal_id')->orderBy('item_no', 'asc');
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    /**
     * Recalculate total quantity based on actual scanned items
     */
    public function recalculateQuantities(): void
    {
        $total = $this->items()->count();
        $this->update([
            'total_quantity' => $total,
        ]);
    }
}
