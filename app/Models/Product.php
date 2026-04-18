<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "sku",
        "store_id",
        "category_id",
        "subcategory_id",
        "product_category_id",
        "price",
        "offer_price",
        "stock",
        "image",
        "status",
    ];

    protected $casts = [
        "price" => "decimal:2",
        "offer_price" => "decimal:2",
        "status" => "boolean",
    ];

    public function store() { return $this->belongsTo(Store::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function subcategory() { return $this->belongsTo(SubCategory::class); }
    public function productCategory() { return $this->belongsTo(ProductCategory::class, "product_category_id"); }
    public function translations() { return $this->hasMany(ProductTranslation::class); }
    public function reviews() { return $this->hasMany(ProductReview::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class)->latest("id"); }
    public function wishlistedBy() { return $this->belongsToMany(User::class, "wishlists")->withTimestamps(); }

    public function translation()
    {
        return $this->hasOne(ProductTranslation::class)
            ->where("locale", app()->getLocale());
    }

    public function getNameAttribute(): ?string
    {
        return optional($this->translation)->name
            ?? optional($this->translations->first())->name;
    }

    public function getCurrentPriceAttribute(): string
    {
        return (string) ($this->offer_price ?: $this->price);
    }

    public function scopeActive($query) { return $query->where("status", true); }
    public function averageRating(): float { return round((float) $this->reviews()->avg("rating"), 1); }

    public function adjustStock(int $quantity, string $direction, ?User $user = null, ?string $reason = null): void
    {
        $before = (int) $this->stock;
        $after = match ($direction) {
            "in" => $before + $quantity,
            "out" => max(0, $before - $quantity),
            default => throw new \InvalidArgumentException("Invalid stock movement direction."),
        };
        $this->update(["stock" => $after]);
        $this->stockMovements()->create([
            "user_id" => $user?->id,
            "direction" => $direction,
            "quantity" => $quantity,
            "stock_before" => $before,
            "stock_after" => $after,
            "reason" => $reason,
        ]);
    }
}
