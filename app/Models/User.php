<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory, MustVerifyEmail, Notifiable, HasApiTokens;

    protected $fillable = [
        'email',
        'password',
        'role_id',
        'store_id',
        'is_active',
        'admin_permissions',
        'remember_token',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'admin_permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // ✅ User belongs to store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ✅ All translations
    public function translations()
    {
        return $this->hasMany(UserTranslation::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistProducts()
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    // ✅ Current locale translation
    public function translation()
    {
        return $this->hasOne(UserTranslation::class)
            ->where('locale', app()->getLocale());
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isStore()
    {
        return $this->role?->name === 'store';
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    public function isSubAdmin(): bool
    {
        return $this->role?->name === 'sub_admin';
    }

    public function isCustomer()
    {
        return $this->role?->name === 'user';
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role?->name, $roles, true);
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->isSubAdmin()) {
            return false;
        }

        return in_array($permission, $this->admin_permissions ?? [], true);
    }

    // ✅ Accessor (clean usage)
    public function getNameAttribute()
    {
        return optional($this->translation)->name
            ?? optional($this->translations->first())->name;
    }

    public function getAddressAttribute()
    {
        return optional($this->translation)->address
            ?? optional($this->translations->first())->address;
    }

    public function syncTranslation(array $attributes, ?string $locale = null): void
    {
        $payload = array_filter([
            'name' => $attributes['name'] ?? null,
            'address' => $attributes['address'] ?? null,
        ], static fn ($value) => $value !== null);

        if ($payload === []) {
            return;
        }

        $this->translations()->updateOrCreate(
            ['locale' => $locale ?? app()->getLocale()],
            $payload
        );
    }
}
