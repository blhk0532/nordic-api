<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenKommuners\RelationPages;

use App\Models\SwedenPersoner;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use gheith3\FilamentRelationPages\RelationPage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class KommunPersonerPage extends RelationPage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Personer';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedUsers;

    protected static bool $isLazy = true;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) SwedenPersoner::where('kommun', $ownerRecord->kommun)->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SwedenPersoner::query()->where('kommun', $this->ownerRecord->kommun)
            )
            ->columns([
                TextColumn::make('fornamn')
                    ->label('Förnamn')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('efternamn')
                    ->label('Efternamn')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alder')
                    ->label('Ålder')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('kon')
                    ->label('Kön')
                    ->sortable(),
                TextColumn::make('adress')
                    ->label('Adress')
                    ->searchable(),
                TextColumn::make('postnummer')
                    ->label('Postnr')
                    ->searchable(),
                TextColumn::make('postort')
                    ->label('Postort')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('telefon')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('civilstand')
                    ->label('Civilstånd')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('efternamn')
            ->paginated([25, 50, 100, 200])
            ->defaultPaginationPageOption(25);
    }

    public function render(): View
    {
        return view('filament.resources.sweden-kommuners.kommun-personer-page');
    }
}
