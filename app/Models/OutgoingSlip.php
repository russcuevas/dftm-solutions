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
        return $this->hasMany(InventoryItem::class, 'outgoing_slip_id')->orderBy('item_no', 'asc');
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}
