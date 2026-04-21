<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\AuthRole;
use App\Exports\SwedenPostorterExporter;
use App\Filament\Resources\SwedenPostorters\SwedenPostorterResource;
use App\Models\SwedenPostorter;
use Waad\FilamentImportWizard\Actions\ImportWizardAction as ExcelImportAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SwedenPostorterWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getTableRecordTitle(Model $record): ?string
    {
        return ' ';
    }

    protected function getTableHeader(): View|Htmlable|null
    {
        return null;
    }

    public function getHeading(): ?string
    {
        return ' ';
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return null;
    }

    public function table(Table $table): Table
    {
        $maxPersoner = SwedenPostorter::max('personer') ?: 1;

        return $table
            ->query(SwedenPostorterResource::getEloquentQuery())
            ->heading('')
            ->columns([
                TextColumn::make('postort')
                    ->label('Postort')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kommun')
                    ->label('Kommun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lan')
                    ->label('Län')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('personer')
                    ->label('Personer')
                    ->html()
                    ->sortable()
                    ->state(function (SwedenPostorter $record) use ($maxPersoner): string {
                        $val = $record->personer ?? 0;
                        $pct = (int) round(($val / $maxPersoner) * 100);
                        $formatted = number_format($val);

                        return '<div style="display:flex;align-items:center;gap:6px;min-width:130px">'
                            .'<div style="flex:1;background-color:rgb(229 231 235);border-radius:9999px;height:5px;overflow:hidden">'
                            .'<div style="background-color:rgb(99 102 241);height:5px;width:'.$pct.'%"></div>'
                            .'</div>'
                            .'<span style="font-size:0.75rem;min-width:3.5rem;text-align:right">'.$formatted.'</span>'
                            .'</div>';
                    }),
                TextColumn::make('postnummer')
                    ->label('Postnr')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gator')
                    ->label('Gator')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('adresser')
                    ->label('Adresser')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('foretag')
                    ->label('Företag')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latitude')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('longitude')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                Action::make('create')
                    ->label(' ')
                    ->color('')
                    ->icon('heroicon-o-plus-circle'),
                ExcelImportAction::make()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->label('CSV'),
                static::importSqlAction(),
                ExportAction::make()
                    ->label('CSV')
                    ->visible(function () {
                        $role = auth()->user()->role;
                        if ($role instanceof AuthRole) {
                            return $role === AuthRole::Super;
                        }
                        // Role is string - normalize legacy values
                        $normalizedRole = match ($role) {
                            'super_admin', 'superadmin' => 'super',
                            default => $role,
                        };

                        return $normalizedRole === 'super';
                    })
                    ->exporter(SwedenPostorterExporter::class)
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('danger'),
                static::exportSqlAction(),
            ]);
    }

    private static function exportSqlAction(): Action
    {
        return Action::make('exportSql')
            ->label('SQL')
            ->visible(function () {
                $role = auth()->user()->role;
                if ($role instanceof AuthRole) {
                    return $role === AuthRole::Super;
                }
                // Role is string - normalize legacy values
                $normalizedRole = match ($role) {
                    'super_admin', 'superadmin' => 'super',
                    default => $role,
                };

                return $normalizedRole === 'super';
            })
            ->icon('heroicon-o-arrow-up-on-square')
            ->color('danger')
            ->action(function () {
                return self::handleSqlExport();
            });
    }

    private static function handleSqlExport()
    {
        try {
            $tableName = 'sweden_postorter';
            $rows = DB::table($tableName)->get();

            if ($rows->isEmpty()) {
                Notification::make()
                    ->warning()
                    ->title('Export Failed')
                    ->body('No data to export.')
                    ->send();

                return null;
            }

            $columns = array_keys((array) $rows->first());

            $sql = "INSERT IGNORE INTO `{$tableName}` (`".implode('`, `', $columns)."`) VALUES \n";

            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($columns as $column) {
                    $value = $row->{$column} ?? null;
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } elseif (is_numeric($value)) {
                        $rowValues[] = $value;
                    } else {
                        $rowValues[] = "'".addslashes($value)."'";
                    }
                }
                $values[] = '    ('.implode(', ', $rowValues).')';
            }

            $sql .= implode(",\n", $values).";\n";

            $filename = "{$tableName}_export_".now()->format('Y-m-d_H-i-s').'.sql';
            $filepath = storage_path('app/'.$filename);

            file_put_contents($filepath, $sql);

            return response()->download($filepath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Export Failed')
                ->body($e->getMessage())
                ->send();

            return null;
        }
    }

    private static function importSqlAction(): Action
    {
        return Action::make('importSql')
            ->label('SQL')
            ->icon('heroicon-o-arrow-down-on-square')
            ->color('success')
            ->schema([
                FileUpload::make('file')
                    ->label('SQL File')
                    ->acceptedFileTypes(['application/sql', 'text/plain', '.sql'])
                    ->storeFiles(false)
                    ->maxSize(1048576)
                    ->required(),
            ])
            ->action(function (array $data): void {
                self::handleSqlImport($data);
            });
    }

    private static function handleSqlImport(array $data): void
    {
        try {
            $files = $data['file'] ?? [];
            if (empty($files)) {
                throw new \Exception('No file uploaded');
            }
            $filePath = is_array($files) ? ($files[0] ?? null) : $files;
            if (! $filePath) {
                throw new \Exception('No file uploaded');
            }
            $fullPath = storage_path('app/public/'.$filePath);
            if (! file_exists($fullPath)) {
                $fullPath = storage_path('app/'.$filePath);
            }
            if (! file_exists($fullPath)) {
                throw new \Exception('File not found: '.$filePath);
            }
            $sqlContent = file_get_contents($fullPath);
            if (! $sqlContent) {
                throw new \Exception('Could not read file');
            }
            $processedSql = self::processSqlForSafeImport($sqlContent, 'sweden_postorter');
            DB::unprepared($processedSql);
            Notification::make()
                ->success()
                ->title('Import Successful')
                ->body('SQL data imported safely.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Import Failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    private static function processSqlForSafeImport(string $sql, ?string $targetTable = null): string
    {
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';

        for ($i = 0, $len = strlen($sql); $i < $len; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if (($char === "'" || $char === '"') && $prev !== '\\') {
                if (! $inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }

            if ($char === ';' && ! $inString) {
                $trimmed = trim($current);
                if (! empty($trimmed)) {
                    $statements[] = $trimmed;
                }
                $current = '';
            } else {
                $current .= $char;
            }
        }

        $trimmed = trim($current);
        if (! empty($trimmed)) {
            $statements[] = $trimmed;
        }

        $processed = [];
        foreach ($statements as $stmt) {
            $upper = strtoupper(ltrim($stmt));

            if (str_starts_with($upper, 'DROP TABLE') ||
                str_starts_with($upper, 'TRUNCATE') ||
                str_starts_with($upper, 'DELETE FROM')) {
                continue;
            }

            if (preg_match('/^\s*INSERT\s+INTO/i', $stmt)) {
                $stmt = preg_replace('/^\s*INSERT\s+INTO/i', 'INSERT IGNORE INTO', $stmt);
            }

            if (preg_match('/^\s*CREATE\s+TABLE/i', $stmt)) {
                $stmt = preg_replace('/^\s*CREATE\s+TABLE/i', 'CREATE TABLE IF NOT EXISTS', $stmt);
            }

            if ($targetTable) {
                $stmt = preg_replace(
                    '/(INSERT\s+(?:IGNORE\s+)?INTO|CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?|ALTER\s+TABLE)\s+[`"\']?[^`"\s(]+[`"\']?/i',
                    '$1 `'.$targetTable.'`',
                    $stmt
                );
            }

            $processed[] = $stmt;
        }

        return implode(";\n", $processed).";\n";
    }
}
