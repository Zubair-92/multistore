<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['store_category_id', 'locale', 'store_category'];

    public function storeCategory()
    {
        return $this->belongsTo(StoreCategory::class);
    }
}
