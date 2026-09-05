<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'slip_no',
        'batch_no',
        'company_name',
        'date_delivered',
        'item_description',
        'brand',
        'model',
        'total_quantity',
        'in_stock_quantity',
        'outgoing_quantity',
        'status',
        'notes',
        'encoded_by',
    ];

    protected $casts = [
        'date_delivered' => 'date',
        'total_quantity' => 'integer',
        'in_stock_quantity' => 'integer',
        'outgoing_quantity' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'batch_id')->orderBy('item_no', 'asc');
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    /**
     * Recalculate quantities based on actual items
     */
    public function recalculateQuantities(): void
    {
        $total = $this->items()->count();
        $inStock = $this->items()->where('stock_status', 'IN_STOCK')->count();
        $outgoing = $this->items()->whereIn('stock_status', ['OUTGOING', 'RELEASED'])->count();

        $this->update([
            'total_quantity' => $total,
            'in_stock_quantity' => $inStock,
            'outgoing_quantity' => $outgoing,
        ]);
    }
}
