<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RingaData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QueueController extends Controller
{
    public function __invoke(Request $request)
    {
        // Get all RingaData records where is_active is true (pending records)
        $records = RingaData::where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'name' => $record->name ?? 'Unknown',
                    'phone' => $record->phone ?? '-',
                    'email' => $record->email ?? '-',
                    'status' => $record->status ?? 'Pending',
                    'outcome' => $record->outcome,
                    'created_at' => $record->created_at?->toIso8601String(),
                    'updated_at' => $record->updated_at?->toIso8601String(),
                ];
            });

        $selectedRecordId = $records->isNotEmpty() ? $records->first()['id'] : null;

        return Inertia::render('Queue', [
            'records' => $records,
            'selectedRecordId' => $selectedRecordId,
        ]);
    }
}
