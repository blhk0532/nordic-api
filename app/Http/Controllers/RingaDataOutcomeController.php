<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Outcomes;
use App\Models\RingaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class RingaDataOutcomeController
{
    public function store(Request $request, $tenant, $id)
    {
        $request->validate([
            'outcome' => 'required|string',
            'aterkom_at' => 'nullable|string',
        ]);

        $record = RingaData::find($id);
        if (! $record) {
            return Redirect::back()->with('error', 'Record not found');
        }

        $outcomeName = $request->input('outcome');

        // Try to resolve enum case by name across Outcomes enum
        $outcomeEnum = null;
        foreach (Outcomes::cases() as $case) {
            if ($case->name === $outcomeName) {
                $outcomeEnum = $case;
                break;
            }
        }

        // Fallback: try matching by value
        if (! $outcomeEnum) {
            foreach (Outcomes::cases() as $case) {
                if ($case->value === $outcomeName) {
                    $outcomeEnum = $case;
                    break;
                }
            }
        }

        // If still not found, store raw string
        if ($outcomeEnum) {
            $record->outcome = $outcomeEnum->value;
        } else {
            $record->outcome = $outcomeName;
        }

        $record->attempts = ($record->attempts ?? 0) + 1;

        if ($request->filled('aterkom_at')) {
            // Expect incoming format like "YYYY-MM-DDTHH:MM" or standard SQL datetime
            $val = $request->input('aterkom_at');
            // Normalize T to space if present
            $val = str_replace('T', ' ', $val);
            $record->aterkom_at = $val;
        }

        $record->save();

        return Redirect::back()->with('success', 'Ok');
    }
}
