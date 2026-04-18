<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    use HasFactory;

    protected $fillable = ['logo'];

    public function translations()
    {
        return $this->hasMany(StoreCategoryTranslation::class);
    }

    // ✅ Only current locale translation (for listing & frontend)
    public function translation()
    {
        return $this->hasOne(StoreCategoryTranslation::class)
            ->where('locale', app()->getLocale());
    }
}
