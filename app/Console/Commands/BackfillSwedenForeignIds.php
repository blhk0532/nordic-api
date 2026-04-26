<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:backfill-sweden-foreign-ids')]
#[Description('Backfill foreign IDs for Sweden tables hierarchy')]
class BackfillSwedenForeignIds extends Command
{
    public function handle(): int
    {
        $this->info('Backfilling Sweden foreign IDs...');

        $this->backfillPostorterKommunerId();
        $this->backfillPostnummer();
        $this->backfillGator();
        $this->backfillAdresser();
        $this->backfillPersoner();

        $this->info('Done!');

        return Command::SUCCESS;
    }

    private function backfillPostorterKommunerId(): void
    {
        $this->info('Backfilling sweden_postorter.sweden_kommuner_id...');

        DB::statement('
            UPDATE sweden_postorter
            SET sweden_kommuner_id = sub.id
            FROM (
                SELECT sp.id, sk.id as kommuner_id
                FROM sweden_postorter sp
                INNER JOIN sweden_kommuner sk ON LOWER(sp.kommun) = LOWER(sk.kommun)
                WHERE sp.sweden_kommuner_id IS NULL
            ) sub
            WHERE sweden_postorter.id = sub.id
        ');
    }

    private function backfillPostnummer(): void
    {
        $this->info('Backfilling sweden_postnummer foreign IDs...');

        DB::statement('
            UPDATE sweden_postnummer
            SET 
                sweden_postorter_id = sub.postorter_id,
                sweden_kommuner_id = sub.kommuner_id
            FROM (
                SELECT spn.id, spo.id as postorter_id, sk.id as kommuner_id
                FROM sweden_postnummer spn
                INNER JOIN sweden_postorter spo ON LOWER(spn.postort) = LOWER(spo.postort) AND LOWER(spn.kommun) = LOWER(spo.kommun)
                INNER JOIN sweden_kommuner sk ON LOWER(spn.kommun) = LOWER(sk.kommun)
                WHERE spn.sweden_postorter_id IS NULL OR spn.sweden_kommuner_id IS NULL
            ) sub
            WHERE sweden_postnummer.id = sub.id
        ');
    }

    private function backfillGator(): void
    {
        $this->info('Backfilling sweden_gator foreign IDs...');

        DB::statement('
            UPDATE sweden_gator
            SET 
                sweden_postnummer_id = sub.postnummer_id,
                sweden_postorter_id = sub.postorter_id,
                sweden_kommuner_id = sub.kommuner_id
            FROM (
                SELECT sg.id, spn.id as postnummer_id, spo.id as postorter_id, sk.id as kommuner_id
                FROM sweden_gator sg
                INNER JOIN sweden_postnummer spn ON sg.postnummer = spn.postnummer AND LOWER(sg.kommun) = LOWER(spn.kommun)
                INNER JOIN sweden_postorter spo ON LOWER(sg.postort) = LOWER(spo.postort) AND LOWER(sg.kommun) = LOWER(spo.kommun)
                INNER JOIN sweden_kommuner sk ON LOWER(sg.kommun) = LOWER(sk.kommun)
                WHERE sg.sweden_postnummer_id IS NULL OR sg.sweden_postorter_id IS NULL OR sg.sweden_kommuner_id IS NULL
            ) sub
            WHERE sweden_gator.id = sub.id
        ');
    }

    private function backfillAdresser(): void
    {
        $this->info('Backfilling sweden_adresser foreign IDs...');

        DB::statement('
            UPDATE sweden_adresser
            SET 
                sweden_gator_id = sub.gator_id,
                sweden_postnummer_id = sub.postnummer_id,
                sweden_postorter_id = sub.postorter_id,
                sweden_kommuner_id = sub.kommuner_id
            FROM (
                SELECT sa.id, sg.id as gator_id, spn.id as postnummer_id, spo.id as postorter_id, sk.id as kommuner_id
                FROM sweden_adresser sa
                INNER JOIN sweden_gator sg ON sa.adress = sg.gata AND sa.postnummer = sg.postnummer AND LOWER(sa.kommun) = LOWER(sg.kommun)
                INNER JOIN sweden_postnummer spn ON sa.postnummer = spn.postnummer AND LOWER(sa.kommun) = LOWER(spn.kommun)
                INNER JOIN sweden_postorter spo ON LOWER(sa.postort) = LOWER(spo.postort) AND LOWER(sa.kommun) = LOWER(spo.kommun)
                INNER JOIN sweden_kommuner sk ON LOWER(sa.kommun) = LOWER(sk.kommun)
                WHERE sa.sweden_gator_id IS NULL OR sa.sweden_postnummer_id IS NULL OR sa.sweden_postorter_id IS NULL OR sa.sweden_kommuner_id IS NULL
            ) sub
            WHERE sweden_adresser.id = sub.id
        ');
    }

    private function backfillPersoner(): void
    {
        $this->info('Backfilling sweden_personer foreign IDs...');

        DB::statement('
            UPDATE sweden_personer
            SET 
                sweden_adresser_id = sub.adresser_id,
                sweden_gator_id = sub.gator_id,
                sweden_postnummer_id = sub.postnummer_id,
                sweden_postorter_id = sub.postorter_id,
                sweden_kommuner_id = sub.kommuner_id
            FROM (
                SELECT 
                    sp.id,
                    sa.id as adresser_id,
                    sg.id as gator_id,
                    spn.id as postnummer_id,
                    spo.id as postorter_id,
                    sk.id as kommuner_id
                FROM sweden_personer sp
                INNER JOIN sweden_adresser sa ON sp.adress = sa.adress AND sp.postnummer = sa.postnummer AND LOWER(sp.kommun) = LOWER(sa.kommun)
                INNER JOIN sweden_gator sg ON sp.adress = sg.gata AND sp.postnummer = sg.postnummer AND LOWER(sp.kommun) = LOWER(sg.kommun)
                INNER JOIN sweden_postnummer spn ON sp.postnummer = spn.postnummer AND LOWER(sp.kommun) = LOWER(spn.kommun)
                INNER JOIN sweden_postorter spo ON LOWER(sp.postort) = LOWER(spo.postort) AND LOWER(sp.kommun) = LOWER(spo.kommun)
                INNER JOIN sweden_kommuner sk ON LOWER(sp.kommun) = LOWER(sk.kommun)
                WHERE sp.sweden_adresser_id IS NULL OR sp.sweden_gator_id IS NULL OR sp.sweden_postnummer_id IS NULL OR sp.sweden_postorter_id IS NULL OR sp.sweden_kommuner_id IS NULL
            ) sub
            WHERE sweden_personer.id = sub.id
        ');
    }
}