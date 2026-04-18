<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_category_id',
        'locale',
        'product_category',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
