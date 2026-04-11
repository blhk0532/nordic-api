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

class BackupPostNums implements ShouldQueue
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
        $backupFile = $exportPath."/post_nums_backup_{$timestamp}.sqlite";

        try {
            // Create SQLite database and copy data
            $sqlite = new PDO("sqlite:{$backupFile}");

            // Create table structure
            $createTableSql = '
                CREATE TABLE post_nums (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_nummer TEXT,
                    post_ort TEXT,
                    post_lan TEXT,
                    merinfo_personer_total INTEGER,
                    merinfo_personer_phone_total INTEGER,
                    merinfo_foretag_total INTEGER,
                    merinfo_foretag_phone_total INTEGER,
                    merinfo_personer_saved INTEGER,
                    merinfo_personer_phone_saved INTEGER,
                    merinfo_personer_house_saved INTEGER,
                    merinfo_foretag_saved INTEGER,
                    merinfo_foretag_phone_saved INTEGER,
                    hitta_personer_total INTEGER,
                    hitta_foretag_total INTEGER,
                    hitta_personer_saved INTEGER,
                    hitta_personer_phone_saved INTEGER,
                    hitta_personer_house_saved INTEGER,
                    hitta_foretag_saved INTEGER,
                    ratsit_personer_total INTEGER,
                    ratsit_foretag_total INTEGER,
                    ratsit_personer_saved INTEGER,
                    ratsit_foretag_saved INTEGER,
                    status TEXT,
                    is_active INTEGER,
                    is_personer_active INTEGER,
                    is_foretag_active INTEGER,
                    merinfo_personer_queue INTEGER,
                    merinfo_foretag_queue INTEGER,
                    hitta_personer_queue INTEGER,
                    hitta_foretag_queue INTEGER,
                    ratsit_personer_queue INTEGER,
                    ratsit_foretag_queue INTEGER,
                    created_at TEXT,
                    updated_at TEXT,
                    ratsit_personer_phone_saved INTEGER,
                    ratsit_personer_house_saved INTEGER
                )
            ';
            $sqlite->exec($createTableSql);

            // Get data from MySQL table
            $postNumsData = DB::table('post_nums')->get();

            // Insert data into SQLite
            $insertStmt = $sqlite->prepare('
                INSERT INTO post_nums (
                    id, post_nummer, post_ort, post_lan, merinfo_personer_total,
                    merinfo_personer_phone_total, merinfo_foretag_total, merinfo_foretag_phone_total,
                    merinfo_personer_saved, merinfo_personer_phone_saved, merinfo_personer_house_saved,
                    merinfo_foretag_saved, merinfo_foretag_phone_saved, hitta_personer_total,
                    hitta_foretag_total, hitta_personer_saved, hitta_personer_phone_saved,
                    hitta_personer_house_saved, hitta_foretag_saved, ratsit_personer_total,
                    ratsit_foretag_total, ratsit_personer_saved, ratsit_foretag_saved, status,
                    is_active, is_personer_active, is_foretag_active, merinfo_personer_queue,
                    merinfo_foretag_queue, hitta_personer_queue, hitta_foretag_queue,
                    ratsit_personer_queue, ratsit_foretag_queue, created_at, updated_at,
                    ratsit_personer_phone_saved, ratsit_personer_house_saved
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            foreach ($postNumsData as $record) {
                $insertStmt->execute([
                    $record->id,
                    $record->post_nummer,
                    $record->post_ort,
                    $record->post_lan,
                    $record->merinfo_personer_total,
                    $record->merinfo_personer_phone_total,
                    $record->merinfo_foretag_total,
                    $record->merinfo_foretag_phone_total,
                    $record->merinfo_personer_saved,
                    $record->merinfo_personer_phone_saved,
                    $record->merinfo_personer_house_saved,
                    $record->merinfo_foretag_saved,
                    $record->merinfo_foretag_phone_saved,
                    $record->hitta_personer_total,
                    $record->hitta_foretag_total,
                    $record->hitta_personer_saved,
                    $record->hitta_personer_phone_saved,
                    $record->hitta_personer_house_saved,
                    $record->hitta_foretag_saved,
                    $record->ratsit_personer_total,
                    $record->ratsit_foretag_total,
                    $record->ratsit_personer_saved,
                    $record->ratsit_foretag_saved,
                    $record->status,
                    $record->is_active,
                    $record->is_personer_active,
                    $record->is_foretag_active,
                    $record->merinfo_personer_queue,
                    $record->merinfo_foretag_queue,
                    $record->hitta_personer_queue,
                    $record->hitta_foretag_queue,
                    $record->ratsit_personer_queue,
                    $record->ratsit_foretag_queue,
                    $record->created_at,
                    $record->updated_at,
                    $record->ratsit_personer_phone_saved,
                    $record->ratsit_personer_house_saved,
                ]);
            }

            $recordCount = $postNumsData->count();

            // Log success
            Log::info("Post nums data backup completed successfully. {$recordCount} records backed up to: {$backupFile}");

        } catch (Exception $e) {
            // Log the error
            Log::error('Post nums data backup failed: '.$e->getMessage());

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }
}
