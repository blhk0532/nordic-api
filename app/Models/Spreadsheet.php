<?php

namespace App\Models;

use Database\Factories\SpreadsheetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spreadsheet extends Model
{
    /** @use HasFactory<SpreadsheetFactory> */
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'google_sheet_id',
        'data',
        'team_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'data' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getTotalRowsAttribute(): int
    {
        $data = $this->data;
        if (! isset($data['sheets'])) {
            return 0;
        }
        $firstSheet = collect($data['sheets'])->first();
        if (! isset($firstSheet['cellData']) || ! is_array($firstSheet['cellData'])) {
            return 0;
        }

        return count($firstSheet['cellData']);
    }

    public function getTotalColumnsAttribute(): int
    {
        $data = $this->data;
        if (! isset($data['sheets'])) {
            return 0;
        }
        $firstSheet = collect($data['sheets'])->first();
        if (! isset($firstSheet['cellData']) || ! is_array($firstSheet['cellData'])) {
            return 0;
        }
        $maxCols = 0;
        foreach ($firstSheet['cellData'] as $row) {
            if (is_array($row)) {
                $maxCols = max($maxCols, count($row));
            }
        }

        return $maxCols;
    }
}
