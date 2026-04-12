<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentDueService
{
    public function upcoming(): Builder
    {
        return Payment::where('status', PaymentStatus::UNPAID)
            ->where('next_payment', '>', now()->toDateString());
    }

    public function today(): Builder
    {
        return Payment::where('status', PaymentStatus::UNPAID)
            ->where('next_payment', now()->toDateString());
    }

    public function overdue(): Builder
    {
        return Payment::where('status', PaymentStatus::UNPAID)
            ->where('next_payment', '<', now()->toDateString());
    }

    public function byPriority(): Builder
    {
        return Payment::where('status', PaymentStatus::UNPAID)
            ->where('is_priority', true)
            ->orderBy('next_payment');
    }
}
