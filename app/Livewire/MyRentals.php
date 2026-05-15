<?php

namespace App\Livewire;

use App\Models\Rental;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MyRentals extends Component
{
    use WithPagination;

    public string $filterStatus = 'all';

    public string $search = '';

    public ?int $receiptRentalId = null;

    public function mount(): void
    {
        session()->put('active_portal', 'renter');

        $receiptRentalId = request('receipt');

        if (is_numeric($receiptRentalId) && $this->receiptRentalQuery((int) $receiptRentalId)->exists()) {
            $this->receiptRentalId = (int) $receiptRentalId;
        }
    }

    public function setFilter(string $status): void
    {
        $normalizedStatus = match ($status) {
            'ongoing' => 'active',
            default => $status,
        };

        $this->filterStatus = $normalizedStatus;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteReturnedRental(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = Rental::query()
            ->whereKey($rentalId)
            ->where('renter_id', Auth::id())
            ->where('status', Rental::STATUS_COMPLETED)
            ->firstOrFail();

        $rental->delete();
        $this->resetPage();

        session()->flash('message', 'Returned rental deleted successfully.');
    }

    public function closeReceiptModal(): void
    {
        $this->receiptRentalId = null;
    }

    public function downloadReceipt(): mixed
    {
        abort_unless(Auth::check(), 403);
        abort_if($this->receiptRentalId === null, 404);

        $rental = $this->receiptRentalQuery($this->receiptRentalId)->firstOrFail();
        $receipt = $this->receiptJpeg($rental);

        return response()->streamDownload(function () use ($receipt): void {
            echo $receipt;
        }, 'campusrent-payment-receipt-'.$rental->id.'.jpg', [
            'Content-Type' => 'image/jpeg',
        ]);
    }

    public function render(): View
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);

        $baseQuery = Rental::query()
            ->where('renter_id', Auth::id())
            ->with('item.user');

        if ($this->search !== '') {
            $search = '%'.trim($this->search).'%';
            $baseQuery->where(function ($query) use ($search): void {
                $query->whereHas('item', fn ($itemQuery) => $itemQuery->where('name', 'like', $search))
                    ->orWhereHas('item.user', fn ($ownerQuery) => $ownerQuery->where('name', 'like', $search));
            });
        }

        $allCount = (clone $baseQuery)->count();
        $dueSoonCount = (clone $baseQuery)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->whereBetween('end_date', [now(), now()->addDays(7)])
            ->count();
        $activeCount = (clone $baseQuery)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>', now()->addDays(7))
            ->count();
        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $baseQuery)
            ->where(function ($query): void {
                $query->where('status', 'approved')
                    ->orWhere(function ($futureActiveQuery): void {
                        $futureActiveQuery->where('status', 'active')
                            ->where('start_date', '>', now());
                    });
            })
            ->count();

        $query = clone $baseQuery;

        if ($this->filterStatus === 'pending') {
            $query->where('status', 'pending');
        } elseif ($this->filterStatus === 'approved') {
            $query->where(function ($approvedQuery): void {
                $approvedQuery->where('status', 'approved')
                    ->orWhere(function ($futureActiveQuery): void {
                        $futureActiveQuery->where('status', 'active')
                            ->where('start_date', '>', now());
                    });
            });
        } elseif ($this->filterStatus === 'due_soon') {
            $query->where('status', 'active')
                ->where('start_date', '<=', now())
                ->whereBetween('end_date', [now(), now()->addDays(7)]);
        } elseif ($this->filterStatus === 'active') {
            $query->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>', now()->addDays(7));
        }

        $rentals = $query->orderBy('end_date', 'asc')
            ->paginate(15);
        $receiptRental = $this->receiptRentalId
            ? $this->receiptRentalQuery($this->receiptRentalId)->first()
            : null;

        return view('livewire.my-rentals', [
            'rentals' => $rentals,
            'receiptRental' => $receiptRental,
            'allCount' => $allCount,
            'dueSoonCount' => $dueSoonCount,
            'activeCount' => $activeCount,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
        ]);
    }

    private function receiptRentalQuery(int $rentalId): Builder
    {
        return Rental::query()
            ->whereKey($rentalId)
            ->where('renter_id', Auth::id())
            ->where('payment_status', '!=', Rental::PAYMENT_STATUS_OUTSTANDING)
            ->with('item.user');
    }

    private function receiptJpeg(Rental $rental): string
    {
        $paidAmount = (float) ($rental->paid_amount ?? 0);
        $totalPrice = (float) $rental->total_price;
        $balance = max(0, $totalPrice - $paidAmount);
        $paymentStatus = $rental->payment_status === Rental::PAYMENT_STATUS_FULLY_PAID ? 'Fully Paid' : 'Partial';

        $image = imagecreatetruecolor(900, 1200);

        $white = imagecolorallocate($image, 255, 255, 255);
        $slate = imagecolorallocate($image, 15, 23, 42);
        $muted = imagecolorallocate($image, 100, 116, 139);
        $border = imagecolorallocate($image, 226, 232, 240);
        $blue = imagecolorallocate($image, 37, 99, 235);
        $emerald = imagecolorallocate($image, 4, 120, 87);
        $softEmerald = imagecolorallocate($image, 236, 253, 245);
        $softBlue = imagecolorallocate($image, 239, 246, 255);

        imagefilledrectangle($image, 0, 0, 900, 1200, $white);
        imagefilledrectangle($image, 0, 0, 900, 210, $softBlue);
        imagefilledrectangle($image, 72, 250, 828, 1040, $white);
        imagerectangle($image, 72, 250, 828, 1040, $border);

        imagestring($image, 5, 72, 70, 'CampusRent', $blue);
        imagestring($image, 5, 72, 110, 'Payment Receipt', $slate);
        imagestring($image, 4, 72, 150, 'Receipt #CR-'.$rental->id, $muted);
        imagestring($image, 4, 590, 150, now()->format('M d, Y g:i A'), $muted);

        imagefilledrectangle($image, 112, 290, 788, 370, $softEmerald);
        imagestring($image, 5, 132, 312, $paymentStatus, $emerald);
        imagestring($image, 3, 132, 340, 'Payment confirmed by the lister', $emerald);

        $receiptRows = [
            ['Item', $rental->item->name],
            ['Lister', $rental->item->user->name],
            ['Rental Period', $rental->start_date->format('M d, Y').' - '.$rental->end_date->format('M d, Y')],
            ['Total', 'PHP '.number_format($totalPrice, 2)],
            ['Paid', 'PHP '.number_format($paidAmount, 2)],
            ['Remaining Balance', 'PHP '.number_format($balance, 2)],
            ['Payment Status', $paymentStatus],
        ];

        $y = 430;

        foreach ($receiptRows as [$label, $value]) {
            imagestring($image, 3, 132, $y, $label, $muted);
            imagestring($image, 5, 132, $y + 28, mb_strimwidth($value, 0, 58, '...'), $slate);
            imageline($image, 132, $y + 70, 768, $y + 70, $border);
            $y += 92;
        }

        imagestring($image, 3, 132, 970, 'Keep this image as your proof of payment for this rental.', $muted);

        ob_start();
        imagejpeg($image, null, 92);
        $receipt = (string) ob_get_clean();

        imagedestroy($image);

        return $receipt;
    }
}
