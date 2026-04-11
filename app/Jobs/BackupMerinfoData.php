<?php

declare(strict_types=1);

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PDO;

class BackupMerinfoData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create export directory if it doesn't exist
        $exportPath = database_path('export');
        if (! File::exists($exportPath)) {
            File::makeDirectory($exportPath, 0755, true);
        }

        // Generate filename with timestamp
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFile = $exportPath."/merinfo_data_backup_{$timestamp}.sqlite";

        try {
            // Create SQLite database and copy data
            $sqlite = new PDO("sqlite:{$backupFile}");

            // Create table structure
            $createTableSql = '
                CREATE TABLE merinfo_data (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    personnamn TEXT,
                    alder TEXT,
                    kon TEXT,
                    gatuadress TEXT,
                    postnummer TEXT,
                    postort TEXT,
                    telefon TEXT,
                    karta TEXT,
                    link TEXT,
                    bostadstyp TEXT,
                    bostadspris TEXT,
                    is_active INTEGER,
                    is_telefon INTEGER,
                    is_ratsit INTEGER,
                    is_hus INTEGER,
                    merinfo_personer_total INTEGER,
                    merinfo_foretag_total INTEGER,
                    created_at TEXT,
                    updated_at TEXT
                )
            ';
            $sqlite->exec($createTableSql);

            // Get data from MySQL table
            $merinfoData = DB::table('merinfo_data')->get();

            // Insert data into SQLite
            $insertStmt = $sqlite->prepare('
                INSERT INTO merinfo_data (
                    id, personnamn, alder, kon, gatuadress,
                    postnummer, postort, telefon, karta, link,
                    bostadstyp, bostadspris, is_active, is_telefon,
                    is_ratsit, is_hus, merinfo_personer_total,
                    merinfo_foretag_total, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            foreach ($merinfoData as $record) {
                $insertStmt->execute([
                    $record->id,
                    $record->personnamn,
                    $record->alder,
                    $record->kon,
                    $record->gatuadress,
                    $record->postnummer,
                    $record->postort,
                    $record->telefon,
                    $record->karta,
                    $record->link,
                    $record->bostadstyp,
                    $record->bostadspris,
                    $record->is_active,
                    $record->is_telefon,
                    $record->is_ratsit,
                    $record->is_hus,
                    $record->merinfo_personer_total,
                    $record->merinfo_foretag_total,
                    $record->created_at,
                    $record->updated_at,
                ]);
            }

            $recordCount = $merinfoData->count();

            // Log success
            Log::info("Merinfo data backup completed successfully. {$recordCount} records backed up to: {$backupFile}");

            // TODO: Send notification to user if needed
            // You could send a database notification or broadcast to the user interface
            // Notification::make()->title('Backup Complete')->body("Successfully backed up {$recordCount} records to: {$backupFile}")->success()->sendToDatabase(auth()->user());

        } catch (Exception $e) {
            // Log the error
            Log::error('Merinfo data backup failed: '.$e->getMessage());

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }
}
