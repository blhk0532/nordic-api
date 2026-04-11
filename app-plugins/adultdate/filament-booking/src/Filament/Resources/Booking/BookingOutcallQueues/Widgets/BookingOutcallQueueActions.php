<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Widgets;

use App\Models\BookingOutcallQueue;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;

class BookingOutcallQueueActions extends Widget
{
    public ?string $filter_name = null;

    public ?string $filter_phone = null;

    public ?string $filter_city = null;

    protected string $view = 'filament.resources.booking-outcall-queues.widgets.booking-outcall-queue-actions';

    public function applyFilters(): void
    {
        $this->emit('bookingOutcallQueue.applyFilters', [
            'name' => $this->filter_name,
            'phone' => $this->filter_phone,
            'city' => $this->filter_city,
        ]);

        Notification::make()
            ->title('Filters applied')
            ->success()
            ->send();
    }

    public function clearFilters(): void
    {
        $this->filter_name = null;
        $this->filter_phone = null;
        $this->filter_city = null;

        $this->emit('bookingOutcallQueue.clearFilters');

        Notification::make()
            ->title('Filters cleared')
            ->success()
            ->send();
    }

    public function exportCsv(): void
    {
        $query = BookingOutcallQueue::query();

        if ($this->filter_name) {
            $query->where('name', 'like', '%'.$this->filter_name.'%');
        }
        if ($this->filter_phone) {
            $query->where('phone', 'like', '%'.$this->filter_phone.'%');
        }
        if ($this->filter_city) {
            $query->where('city', 'like', '%'.$this->filter_city.'%');
        }

        $rows = $query->limit(1000)->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'phone' => $r->phone,
                'city' => $r->city,
                'address' => $r->address,
            ];
        })->toArray();

        $csv = implode(',', array_keys($rows[0] ?? ['id', 'name']))."\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(function ($v) {
                return '"'.str_replace('"', '""', (string) $v).'"';
            }, $row))."\n";
        }

        // Store temporary csv and provide download link via notification
        $path = 'exports/booking_outcall_queues_'.time().'.csv';
        Storage::disk(config('filesystems.default'))->put($path, $csv);

        $url = Storage::disk(config('filesystems.default'))->url($path);

        Notification::make()
            ->title('CSV exported')
            ->success()
            ->icon('heroicon-m-download')
            ->body("<a href=\"{$url}\" target=\"_blank\">Download CSV</a>")
            ->send();
    }
}
