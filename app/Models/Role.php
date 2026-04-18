<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'display_name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function ensureDefaults(): void
    {
        if (static::query()->exists()) {
            return;
        }

        foreach ([
            ['name' => 'admin', 'display_name' => 'Administrator'],
            ['name' => 'sub_admin', 'display_name' => 'Sub Administrator'],
            ['name' => 'store', 'display_name' => 'Store Owner'],
            ['name' => 'staff', 'display_name' => 'Staff'],
            ['name' => 'user', 'display_name' => 'User'],
        ] as $role) {
            static::query()->create($role);
        }
    }
}
