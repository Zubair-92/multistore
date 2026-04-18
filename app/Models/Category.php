<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo',
    ];

    // All translations (for admin, edit, etc)
    public function subcategory()
    {
        return $this->hasMany(SubCategory::class);
    }

    // All translations (for admin, edit, etc)
    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    // Only current locale translation (for listing & frontend)
    public function translation()
    {
        return $this->hasOne(CategoryTranslation::class)
                    ->where('locale', app()->getLocale());
    }
}
