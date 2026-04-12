<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Client;
use App\Models\Outcome;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

class StatsService
{
    public function getDashboardData(?string $from = null, ?string $to = null): array
    {
        [$start, $end] = $this->normalizeRange($from, $to);

        $totalIncome = $this->sumPaidPayments($start, $end);
        $totalOutcome = $this->sumOutcomes($start, $end);

        return [
            'stats' => [
                'total_income' => $totalIncome,
                'total_outcome' => $totalOutcome,
                'total_clients' => $this->countClients($start, $end),
                'profit' => $totalIncome - $totalOutcome,
                'incomes_chart' => $this->getIncomeSeries($start, $end),
                'total_upcoming_payments' => $this->countUpcomingPayments(),
            ],
        ];
    }

    private function normalizeRange(?string $from, ?string $to): array
    {
        $start = $from ? Carbon::parse($from)->startOfDay() : null;
        $end = $to ? Carbon::parse($to)->endOfDay() : null;

        if ($start && $end && $start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }

    private function sumPaidPayments(?Carbon $start, ?Carbon $end): float
    {
        return (float) Payment::query()
            ->where('status', PaymentStatus::PAID->value)
            ->when($start, fn (Builder $query) => $query->where('paid_at', '>=', $start))
            ->when($end, fn (Builder $query) => $query->where('paid_at', '<=', $end))
            ->sum('payment_amount');
    }

    private function sumOutcomes(?Carbon $start, ?Carbon $end): float
    {
        return (float) Outcome::query()
            ->when($start, fn (Builder $query) => $query->where('date', '>=', $start))
            ->when($end, fn (Builder $query) => $query->where('date', '<=', $end))
            ->sum('amount');
    }

    private function countClients(?Carbon $start, ?Carbon $end): int
    {
        return Client::query()
            ->when($start, fn (Builder $query) => $query->where('created_at', '>=', $start))
            ->when($end, fn (Builder $query) => $query->where('created_at', '<=', $end))
            ->count();
    }

    private function getIncomeSeries(?Carbon $start, ?Carbon $end): array
    {
        [$chartStart, $chartEnd] = $this->getChartRange($start, $end);

        $dates = CarbonPeriod::create($chartStart, $chartEnd);

        $payments = Payment::query()
            ->selectRaw('DATE(paid_at) as day, SUM(payment_amount) as total')
            ->where('status')
            ->when($chartStart, fn (Builder $query) => $query->where('paid_at', '>=', $chartStart))
            ->when($chartEnd, fn (Builder $query) => $query->where('paid_at', '<=', $chartEnd))
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];

        foreach ($dates as $date) {
            $series[] = (float) ($payments->get($date->toDateString()) ?? 0);
        }

        return $series;
    }

    private function getChartRange(?Carbon $start, ?Carbon $end): array
    {
        $now = Carbon::now();
        $chartStart = $start ? $start->copy()->startOfDay() : $now->copy()->subDays(30)->startOfDay();
        $chartEnd = $end ? $end->copy()->endOfDay() : $now->copy()->endOfDay();

        if ($chartStart->gt($chartEnd)) {
            [$chartStart, $chartEnd] = [$chartEnd, $chartStart];
        }

        return [$chartStart, $chartEnd];
    }

    private function countUpcomingPayments(): int
    {
        return Payment::query()
            ->where('status', PaymentStatus::UNPAID->value)
            ->whereNotNull('next_payment')
            ->whereDate('next_payment', '>=', Carbon::now()->startOfDay())
            ->count();
    }
}
