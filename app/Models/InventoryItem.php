<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'outgoing_slip_id',
        'item_no',
        'brand',
        'model',
        'serial_number',
        'mac_address',
        'box_no',
        'technical_diagnostic',
        'replace_parts',
        'repair_status',
        'stock_status',
        'company_name',
        'customer_name',
        'customer_contact',
        'si_number',
        'dr_number',
        'date_delivered',
        'date_outgoing',
        'notes',
        'encoded_by',
    ];

    protected $casts = [
        'item_no' => 'integer',
        'date_delivered' => 'date',
        'date_outgoing' => 'date',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function outgoingSlip()
    {
        return $this->belongsTo(OutgoingSlip::class, 'outgoing_slip_id');
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    // Helper for status badge styling
    public function getRepairStatusBadgeAttribute(): string
    {
        return match (strtoupper(str_replace(' ', '_', $this->repair_status ?? 'IN_PROCESS'))) {
            'REPAIRED' => 'badge-repaired',
            'BER' => 'badge-ber',
            default => 'badge-in-process',
        };
    }

    public function getStockStatusBadgeAttribute(): string
    {
        return match (strtoupper($this->stock_status ?? 'IN_STOCK')) {
            'IN_STOCK' => 'badge-primary',
            'OUTGOING' => 'badge-warning',
            'RELEASED' => 'badge-secondary',
            default => 'badge-light',
        };
    }
}
