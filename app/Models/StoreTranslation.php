<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'locale',
        'name',         // ✅ added
        'description',
        'address',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
