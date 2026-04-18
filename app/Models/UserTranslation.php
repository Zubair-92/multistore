<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserTranslation extends Model
{
    use HasFactory;

    protected $table = 'user_translations';

    protected $fillable = [
        'user_id',
        'locale',
        'name',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}