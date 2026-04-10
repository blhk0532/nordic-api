<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\RatsitKommun;
use App\Models\RatsitPostort;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PostorterTableWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Alla Postnummer';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(RatsitPostort::query())
            ->columns([
                TextColumn::make('postnummer')
                    ->label('Postnummer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postort')
                    ->label('Ort')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kommun')
                    ->label('Kommun')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('personer_count')
                    ->label('Personer')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state)),
                TextColumn::make('foretag_count')
                    ->label('Företag')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state)),
            ])
            ->filters([
                Filter::make('personer_count')
                    ->label('Min antal personer')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('min_personer')
                            ->numeric()
                            ->label('Min personer'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['min_personer'] ?? null,
                            fn ($q, $min) => $q->where('personer_count', '>=', $min),
                        );
                    }),
                SelectFilter::make('kommun')
                    ->label('Kommun')
                    ->searchable()
                    ->options(fn () => RatsitKommun::pluck('kommun', 'kommun')->toArray()),
            ])
            ->defaultSort('personer_count', 'desc')
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Inga postnummer hittades')
            ->emptyStateDescription('Klicka på en kommun ovan för att visa dess postnummer.');
    }
}
