<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenKommuners\RelationPages;

use App\Models\SwedenPostnummer;
use App\Models\SwedenPostorter;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use gheith3\FilamentRelationPages\RelationPage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class KommunPostnummerPage extends RelationPage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Postnummer';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedEnvelope;

    protected static bool $isLazy = true;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) SwedenPostnummer::where('kommun', $ownerRecord->kommun)->count();
    }

    public function table(Table $table): Table
    {
        $kommunName = $this->ownerRecord->kommun;

        // Preload persons from postorter per postort to fill null persons in postnummer
        $postorterPersoner = SwedenPostorter::where('kommun', $kommunName)
            ->pluck('personer', 'postort')
            ->all();

        return $table
            ->query(
                SwedenPostnummer::query()->where('kommun', $kommunName)
            )
            ->columns([
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('personer')
                    ->label('Personer')
                    ->numeric()
                    ->sortable()
                    ->state(function (SwedenPostnummer $record) use ($postorterPersoner): ?int {
                        return $record->personer ?? ($postorterPersoner[$record->postort] ?? null);
                    })
                    ->placeholder('-'),
                TextColumn::make('foretag')
                    ->label('Företag')
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
            ])
            ->defaultSort('postnummer')
            ->paginated([25, 50, 100, 200])
            ->defaultPaginationPageOption(25);
    }

    public function render(): View
    {
        return view('filament.resources.sweden-kommuners.kommun-postnummer-page');
    }
}
