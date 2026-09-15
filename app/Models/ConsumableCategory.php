<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sort_order',
    ];

    public function items()
    {
        return $this->hasMany(ConsumableItem::class, 'category_id')->orderBy('sort_order')->orderBy('name');
    }
}
