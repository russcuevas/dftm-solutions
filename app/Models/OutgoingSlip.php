<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutgoingSlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'slip_no',
        'company_name',
        'customer_name',
        'contact_number',
        'date_delivered',
        'date_released',
        'si_number',
        'dr_number',
        'batch_no',
        'item_description',
        'brand',
        'model',
        'box_no',
        'total_quantity',
        'status',
        'notes',
        'encoded_by',
    ];

    protected $casts = [
        'date_delivered' => 'date',
        'date_released' => 'date',
        'total_quantity' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'outgoing_slip_id')
            ->orderByRaw("CASE WHEN box_no IS NULL OR box_no = '' THEN 1 ELSE 0 END ASC")
            ->orderByRaw("LENGTH(box_no) ASC, box_no ASC")
            ->orderBy('item_no', 'asc')
            ->orderBy('id', 'asc');
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}
