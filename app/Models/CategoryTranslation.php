<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    // Table name (only needed if not plural default)
    protected $table = 'category_translations';

    // Mass assignable fields
    protected $fillable = [
        'category_id',
        'locale',
        'category', // translated category name
    ];

    // Enable timestamps
    public $timestamps = true;

    // Relationship to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
