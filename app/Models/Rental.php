<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_OUTSTANDING = 'outstanding';

    public const PAYMENT_STATUS_PARTIAL = 'partial';

    public const PAYMENT_STATUS_FULLY_PAID = 'fully_paid';

    protected $fillable = [
        'item_id',
        'renter_id',
        'start_date',
        'end_date',
        'total_price',
        'paid_amount',
        'payment_status',
        'status',
        'approved_at',
        'active_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'total_price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'active_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(RentalMessage::class);
    }

    public function applyPayment(float $paymentAmount): void
    {
        $currentPaidAmount = (float) ($this->paid_amount ?? 0);
        $nextPaidAmount = min((float) $this->total_price, $currentPaidAmount + $paymentAmount);

        $paymentStatus = self::PAYMENT_STATUS_OUTSTANDING;

        if ($nextPaidAmount >= (float) $this->total_price) {
            $paymentStatus = self::PAYMENT_STATUS_FULLY_PAID;
        } elseif ($nextPaidAmount > 0) {
            $paymentStatus = self::PAYMENT_STATUS_PARTIAL;
        }

        $this->update([
            'paid_amount' => $nextPaidAmount,
            'payment_status' => $paymentStatus,
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,approved,active,completed,cancelled'],
            'payment_status' => ['required', 'in:outstanding,partial,fully_paid'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }
}
