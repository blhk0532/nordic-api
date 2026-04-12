<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\IncomeStatus;
use App\Models\Income;

class IncomeStatusService
{
    public static function recalculateIncomeStatusFor(int $incomeId): void
    {
        /** @var Income $income */
        $income = Income::find($incomeId);
        if (! $income) {
            return;
        }

        $totalPaid = $income->total_paid;
        $amount = $income->final_amount > 0 ? $income->final_amount : $income->amount;

        if ($totalPaid === 0) {
            $status = IncomeStatus::PENDING;
        } elseif ($totalPaid < $amount) {
            $status = IncomeStatus::PARTIAL;
        } else {
            $status = IncomeStatus::COMPLETED;
        }

        $income->update(['status' => $status]);
    }
}
