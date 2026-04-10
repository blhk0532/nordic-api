<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DatabaseBackupWidget extends Widget
{
    protected string $view = 'filament.widgets.database-backup-widget';

    public ?string $exportTable = null;

    public bool $exportStructure = true;

    public bool $exportData = true;

    public bool $exportIndexes = true;

    protected int|string|array $columnSpan = 'full';

    public function getTableList(): array
    {
        // Return empty array for now to test
        \Log::info('getTableList called', ['trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)]);

        return ['test_table_1', 'test_table_2'];
    }

    public function getTableInfo(string $table): array
    {
        return [
            'rows' => '0',
            'size_mb' => '0.00',
        ];
    }

    public function backupDatabase(): void
    {
        $this->dispatch('notify', [
            'title' => 'Backup Started',
            'body' => 'Full database backup has been queued.',
            'status' => 'success',
        ]);
    }

    public function exportTableData()
    {
        if (! $this->exportTable) {
            $this->dispatch('notify', [
                'title' => 'Error',
                'body' => 'Please select a table to export.',
                'status' => 'error',
            ]);

            return null;
        }

        $this->dispatch('notify', [
            'title' => 'Export Started',
            'body' => "Exporting table: {$this->exportTable}",
            'status' => 'success',
        ]);

        return null;
    }

    public function getImportTableAction()
    {
        return null;
    }

    public function listTables(): void
    {
        $this->dispatch('notify', [
            'title' => 'Database Tables',
            'body' => 'Tables list would appear here',
            'status' => 'info',
        ]);
    }
}
