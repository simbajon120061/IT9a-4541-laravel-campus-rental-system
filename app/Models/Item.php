<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'condition',
        'status',
        'category_id',
        'image_path',
        'admin_removed_by',
        'admin_removal_reason',
        'admin_removal_details',
        'admin_removed_at',
    ];

    protected function casts(): array
    {
        return [
            'admin_removed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_item_id');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(ItemAppeal::class);
    }

    public function latestAppeal(): HasOne
    {
        return $this->hasOne(ItemAppeal::class)->latestOfMany();
    }

    public function removedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_removed_by');
    }

    public function latestRental(): HasOne
    {
        return $this->hasOne(Rental::class)->latestOfMany();
    }

    public function categoryRecord(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->whereHas('user', fn (Builder $query): Builder => $query->whereNull('restricted_at'));
    }

    public function categoryName(): string
    {
        return $this->categoryRecord?->name ?? 'Other';
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path || ! Storage::disk('public')->exists($this->image_path)) {
            return null;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $this->image_path), '/');
    }
}
