<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\SwedenKommuners\SwedenKommunerResource;
use App\Filament\Resources\SwedenKommuners\Tables\SwedenKommunersTable;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class SwedenKommunerWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public $tableRecordsPerPage = 25;

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
        $table = $table
            ->query(SwedenKommunerResource::getEloquentQuery())
            ->heading('');

        $table = SwedenKommunersTable::configure($table);

        $table->paginationMode(PaginationMode::Default)
            ->defaultSort('personer_count', 'desc');

        // Override record actions to add the custom view URL
        return $table->recordActions([
            ViewAction::make()
                ->label('Visa')
                ->icon('heroicon-o-eye')
                ->url(fn (Model $record) => SwedenKommunerResource::getUrl('view', ['record' => $record])),
            EditAction::make(),
        ]);
    }
}
