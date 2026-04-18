<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'logo',
    ];

    // Relation to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productcategory()
    {
        return $this->hasMany(ProductCategory::class);
    }

    // All translations (for admin, edit, etc.)
    public function translations()
    {
        return $this->hasMany(SubCategoryTranslation::class);
    }

    // Only current locale translation (for listing & frontend)
    public function translation()
    {
        return $this->hasOne(SubCategoryTranslation::class)
                    ->where('locale', app()->getLocale());
    }

    // Delete translations when SubCategory is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($subcategory) {
            $subcategory->translations()->delete();
        });
    }
}
