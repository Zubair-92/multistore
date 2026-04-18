<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_category_id',
        'email',
        'phone',
        'logo',
        'approved',
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // ✅ Store belongs to a category
    public function storeCategory()
    {
        return $this->belongsTo(StoreCategory::class);
    }

    // ✅ One store can have multiple users (owner, staff, etc.)
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ✅ All translations (EN, AR, etc.)
    public function translations()
    {
        return $this->hasMany(StoreTranslation::class);
    }

    // ✅ Current locale translation (VERY IMPORTANT for frontend)
    public function translation()
    {
        return $this->hasOne(StoreTranslation::class)
            ->where('locale', app()->getLocale());
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Optional but PRO level)
    |--------------------------------------------------------------------------
    */

    // ✅ Fallback if translation missing
    public function getNameAttribute()
    {
        return optional($this->translation)->name 
            ?? optional($this->translations->first())->name;
    }

    public function getDescriptionAttribute()
    {
        return optional($this->translation)->description 
            ?? optional($this->translations->first())->description;
    }

    public function getAddressAttribute()
    {
        return optional($this->translation)->address
            ?? optional($this->translations->first())->address;
    }

    public function isApproved(): bool
    {
        return (bool) $this->approved;
    }
}
