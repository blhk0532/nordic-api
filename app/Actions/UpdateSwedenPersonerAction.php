<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SwedenPersoner;
use Illuminate\Support\Facades\DB;

class UpdateSwedenPersonerAction
{
    /**
     * Update kommun and lan on a SwedenPersoner record.
     *
     * @param  bool  $force  When false, only fills fields that are currently null.
     */
    public static function execute(SwedenPersoner $record, bool $force = false): bool
    {
        if (! $force && $record->kommun !== null && $record->lan !== null) {
            return false;
        }

        $row = null;

        // 1. Lookup by postnummer (most reliable)
        if ($record->postnummer !== null) {
            $normalised = preg_replace('/\D/', '', $record->postnummer);

            $row = DB::table('sweden_gator')
                ->where('postnummer', $normalised)
                ->whereNotNull('kommun')
                ->select('kommun', 'lan')
                ->first();
        }

        // 2. Fall back to postort
        if ($row === null && $record->postort !== null) {
            $row = DB::table('sweden_gator')
                ->whereRaw('LOWER(postort) = ?', [mb_strtolower(trim($record->postort))])
                ->whereNotNull('kommun')
                ->select('kommun', 'lan')
                ->first();
        }

        // 3. Fall back to sweden_kommuner by existing kommun value to fill only lan
        if ($row === null && $record->kommun !== null) {
            $kommunRow = DB::table('sweden_kommuner')
                ->whereRaw('LOWER(kommun) = ?', [mb_strtolower(trim($record->kommun))])
                ->whereNotNull('lan')
                ->select('kommun', 'lan')
                ->first();

            if ($kommunRow !== null) {
                $row = $kommunRow;
            }
        }

        if ($row === null) {
            return false;
        }

        $updates = [];

        if ($force || $record->kommun === null) {
            $updates['kommun'] = $row->kommun;
        }

        if ($force || $record->lan === null) {
            $updates['lan'] = $row->lan ?? null;
        }

        if ($updates === []) {
            return false;
        }

        $updates['updated_at'] = now();

        DB::table('sweden_personer')
            ->where('id', $record->id)
            ->update($updates);

        return true;
    }
}
