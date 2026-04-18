<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = ['category_id','sub_category_id','logo'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    // Relation to Category
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    // All translations
    public function translations()
    {
        return $this->hasMany(ProductCategoryTranslation::class);
    }

    // Only current locale
    public function translation()
    {
        return $this->hasOne(ProductCategoryTranslation::class)
                    ->where('locale', app()->getLocale());
    }
}
