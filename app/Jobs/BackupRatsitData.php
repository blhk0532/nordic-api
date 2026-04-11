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

class BackupRatsitData implements ShouldQueue
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
        $backupFile = $exportPath."/ratsit_data_backup_{$timestamp}.sqlite";

        try {
            // Create SQLite database and copy data
            $sqlite = new PDO("sqlite:{$backupFile}");

            // Create table structure
            $createTableSql = '
                CREATE TABLE ratsit_data (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    gatuadress TEXT,
                    postnummer TEXT,
                    postort TEXT,
                    forsamling TEXT,
                    kommun TEXT,
                    lan TEXT,
                    adressandring TEXT,
                    telfonnummer TEXT,
                    stjarntacken TEXT,
                    fodelsedag TEXT,
                    personnummer TEXT,
                    alder TEXT,
                    kon TEXT,
                    civilstand TEXT,
                    fornamn TEXT,
                    efternamn TEXT,
                    personnamn TEXT,
                    telefon TEXT,
                    epost_adress TEXT,
                    agandeform TEXT,
                    bostadstyp TEXT,
                    boarea TEXT,
                    byggar TEXT,
                    fastighet TEXT,
                    personer TEXT,
                    foretag TEXT,
                    grannar TEXT,
                    fordon TEXT,
                    hundar TEXT,
                    bolagsengagemang TEXT,
                    longitude TEXT,
                    latitud TEXT,
                    google_maps TEXT,
                    google_streetview TEXT,
                    ratsit_se TEXT,
                    is_active INTEGER,
                    created_at TEXT,
                    updated_at TEXT,
                    kommun_ratsit TEXT,
                    is_queued INTEGER
                )
            ';
            $sqlite->exec($createTableSql);

            // Get data from MySQL table
            $ratsitData = DB::table('ratsit_data')->get();

            // Insert data into SQLite
            $insertStmt = $sqlite->prepare('
                INSERT INTO ratsit_data (
                    id, gatuadress, postnummer, postort, forsamling, kommun, lan,
                    adressandring, telfonnummer, stjarntacken, fodelsedag, personnummer,
                    alder, kon, civilstand, fornamn, efternamn, personnamn, telefon,
                    epost_adress, agandeform, bostadstyp, boarea, byggar, fastighet,
                    personer, foretag, grannar, fordon, hundar, bolagsengagemang,
                    longitude, latitud, google_maps, google_streetview, ratsit_se,
                    is_active, created_at, updated_at, kommun_ratsit, is_queued
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            foreach ($ratsitData as $record) {
                $insertStmt->execute([
                    $record->id,
                    $record->gatuadress,
                    $record->postnummer,
                    $record->postort,
                    $record->forsamling,
                    $record->kommun,
                    $record->lan,
                    $record->adressandring,
                    $record->telfonnummer,
                    $record->stjarntacken,
                    $record->fodelsedag,
                    $record->personnummer,
                    $record->alder,
                    $record->kon,
                    $record->civilstand,
                    $record->fornamn,
                    $record->efternamn,
                    $record->personnamn,
                    $record->telefon,
                    $record->epost_adress,
                    $record->agandeform,
                    $record->bostadstyp,
                    $record->boarea,
                    $record->byggar,
                    $record->fastighet,
                    $record->personer,
                    $record->foretag,
                    $record->grannar,
                    $record->fordon,
                    $record->hundar,
                    $record->bolagsengagemang,
                    $record->longitude,
                    $record->latitud,
                    $record->google_maps,
                    $record->google_streetview,
                    $record->ratsit_se,
                    $record->is_active,
                    $record->created_at,
                    $record->updated_at,
                    $record->kommun_ratsit,
                    $record->is_queued,
                ]);
            }

            $recordCount = $ratsitData->count();

            // Log success
            Log::info("Ratsit data backup completed successfully. {$recordCount} records backed up to: {$backupFile}");

        } catch (Exception $e) {
            // Log the error
            Log::error('Ratsit data backup failed: '.$e->getMessage());

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }
}
