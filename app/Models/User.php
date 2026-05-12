<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    public const array PROGRAMS = [
        'Accounting Education',
        'Criminal Justice Education',
        'Computing Education',
        'Teacher Education',
        'Engineering Education',
        'Arts and Sciences Education',
        'Business Administration Education',
        'Graduate School',
    ];

    public const array SCHOOL_LEVELS = [
        '1st Year',
        '2nd Year',
        '3rd Year',
        '4th Year',
        '5th Year',
    ];

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'student_id',
        'phone_number',
        'secondary_phone_number',
        'course',
        'year_level',
        'department',
        'bio',
        'rating',
        'is_verified_student',
        'is_admin',
        'warning_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified_student' => 'boolean',
            'is_admin' => 'boolean',
            'warning_count' => 'integer',
            'restricted_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'renter_id');
    }

    public function sentRentalMessages(): HasMany
    {
        return $this->hasMany(RentalMessage::class, 'sender_id');
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    // Helper methods
    public function getAverageRatingAttribute(): float
    {
        return (float) ($this->rating ?? 0);
    }

    public function isStudentVerified(): bool
    {
        return (bool) $this->is_verified_student;
    }

    public function isAdministrator(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isRestricted(): bool
    {
        return ! $this->trashed() && $this->restricted_at !== null;
    }

    public function accountStatusLabel(): string
    {
        if ($this->trashed()) {
            return 'Deactivated';
        }

        if ($this->isRestricted()) {
            return 'Restricted';
        }

        return 'Active';
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path && Storage::disk($this->profilePhotoDisk())->exists($this->profile_photo_path)) {
            return '/storage/'.ltrim(str_replace('\\', '/', $this->profile_photo_path), '/');
        }

        return $this->defaultProfilePhotoUrl();
    }

    protected function rating(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): float => max(0, min(5, round((float) $value, 1)))
        );
    }
}
