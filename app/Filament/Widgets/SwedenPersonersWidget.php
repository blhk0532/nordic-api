<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\RatsitKommun;
use App\Models\SwedenPersoner;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Leek\FilamentHeaderFilters\Concerns\HasHeaderFilters;

class SwedenPersonersWidget extends TableWidget
{
    use HasHeaderFilters;

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Sverige Personer';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SwedenPersoner::query())
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('adress')
                    ->label('Adress')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->headerFilter(
                        Filter::make('adress')
                            ->schema([
                                TextInput::make('adress')
                                    ->placeholder('Sök adress...'),
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                return $query->when(
                                    $data['adress'] ?? null,
                                    fn (Builder $q, $value): Builder => $q->where('adress', 'ilike', "%{$value}%")
                                );
                            })
                    ),
                TextColumn::make('postnummer')
                    ->label('Postnr')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->headerFilter(
                        SelectFilter::make('postnummer')
                            ->options(fn () => SwedenPersoner::distinct()->pluck('postnummer', 'postnummer')->take(100)->toArray())
                            ->native(false)
                            ->placeholder('Alla')
                    ),
                TextColumn::make('postort')
                    ->label('Postort')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->headerFilter(
                        SelectFilter::make('postort')
                            ->options(fn () => SwedenPersoner::distinct()->pluck('postort', 'postort')->take(100)->toArray())
                            ->native(false)
                            ->placeholder('Alla')
                    ),
                TextColumn::make('fornamn')
                    ->label('Förnamn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('efternamn')
                    ->label('Efternamn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('personnamn')
                    ->label('Namn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('telefon')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('kommun')
                    ->label('Kommun')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->headerFilter(
                        SelectFilter::make('kommun')
                            ->options(fn () => RatsitKommun::pluck('kommun', 'kommun')->toArray())
                            ->native(false)
                            ->placeholder('Alla')
                    ),
                TextColumn::make('swedenKommun.lan')
                    ->label('Län')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kon')
                    ->label('Kön')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('alder')
                    ->label('Ålder')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_hus')
                    ->label('Hus')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_owner')
                    ->label('Ägare')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_done')
                    ->label('Klar')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersFormWidth(Width::FourExtraLarge)
            ->filtersFormColumns(4)
            ->filters([
                SelectFilter::make('telefon')
                    ->label('Telefon')
                    ->options([
                        'yes' => 'Ja',
                        'no' => 'Nej',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'yes' => $query->whereNotNull('telefon'),
                            'no' => $query->whereNull('telefon'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('is_hus')
                    ->label('Hus')
                    ->options([
                        'yes' => 'Ja',
                        'no' => 'Nej',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'yes' => $query->where('is_hus', true),
                            'no' => $query->where('is_hus', false),
                            default => $query,
                        };
                    }),
                SelectFilter::make('is_active')
                    ->label('Aktiv')
                    ->options([
                        'yes' => 'Ja',
                        'no' => 'Nej',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'yes' => $query->where('is_active', true),
                            'no' => $query->where('is_active', false),
                            default => $query,
                        };
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Ingen data')
            ->emptyStateDescription('Ingen data tillgänglig för Personer.');
    }
}
