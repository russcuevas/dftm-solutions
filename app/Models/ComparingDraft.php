<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparingDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'company_name',
        'brand',
        'model',
        'batch_no',
        'date_delivered',
        'status',
        'sound_enabled',
        'rows_data',
        'verified_count',
        'total_rows',
    ];

    protected $casts = [
        'sound_enabled' => 'boolean',
        'rows_data' => 'array',
        'verified_count' => 'integer',
        'total_rows' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
