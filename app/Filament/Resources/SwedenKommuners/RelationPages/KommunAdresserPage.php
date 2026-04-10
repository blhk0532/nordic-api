<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenKommuners\RelationPages;

use App\Models\SwedenAdresser;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use gheith3\FilamentRelationPages\RelationPage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class KommunAdresserPage extends RelationPage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Adresser';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedHome;

    protected static bool $isLazy = true;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) SwedenAdresser::where('kommun', $ownerRecord->kommun)->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SwedenAdresser::query()->where('kommun', $this->ownerRecord->kommun)
            )
            ->columns([
                TextColumn::make('adress')
                    ->label('Adress')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postnummer')
                    ->label('Postnr')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postort')
                    ->label('Postort')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lan')
                    ->label('Län')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('personer')
                    ->label('Personer')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('företag')
                    ->label('Företag')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ratsit_link')
                    ->label('Ratsit')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('adress')
            ->paginated([25, 50, 100, 200])
            ->defaultPaginationPageOption(25);
    }

    public function render(): View
    {
        return view('filament.resources.sweden-kommuners.kommun-adresser-page');
    }
}
