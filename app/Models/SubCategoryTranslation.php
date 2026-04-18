<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_category_id',
        'locale',
        'sub_category', // translated subcategory name
    ];

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
