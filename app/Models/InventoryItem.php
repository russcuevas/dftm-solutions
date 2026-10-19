<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    public const TECHNICAL_DIAGNOSTICS = [
        'Tested and clean',
        'NO POWER',
        'INTERMITTENT POWER',
        'NO WIFI DETECTED',
        'DEFECTIVE LED LIGHT',
        'DEFECTIVE LAN PORT',
        'LOS PON PROBLEM',
        'BLINKING PON PROBLEM',
        'CANNOT RESET',
        'BROKEN WIFI ANTENNA',
        'DISCOLORATION CASING',
        'CORRODED BOARD',
        'RUSTY BOARD',
        'CPU PROBLEM/NO POWER',
    ];

    public const REPLACED_PARTS = [
        'SMD CAPACITOR',
        'SMD RESISTOR',
        'ON/OFF SWITCH',
        'PUSH BUTTON',
        'LED LIGHT',
        'LAN PORT',
        'WIFI IC',
        'POWER IC',
        'LASER DIODE',
        'ANTENNA',
        'CASING',
        'N/A',
    ];

    protected $fillable = [
        'transmittal_id',
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

    public function transmittal()
    {
        return $this->belongsTo(Transmittal::class, 'transmittal_id');
    }

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
            'GOOD' => 'badge-repaired',
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
