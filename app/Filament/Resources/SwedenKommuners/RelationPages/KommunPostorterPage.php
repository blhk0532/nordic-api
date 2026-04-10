<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenKommuners\RelationPages;

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

class KommunPostorterPage extends RelationPage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Postorter';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedBuildingOffice;

    protected static bool $isLazy = true;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) SwedenPostorter::where('kommun', $ownerRecord->kommun)->count();
    }

    public function table(Table $table): Table
    {
        $maxPersoner = SwedenPostorter::where('kommun', $this->ownerRecord->kommun)->max('personer') ?: 1;

        return $table
            ->query(
                SwedenPostorter::query()->where('kommun', $this->ownerRecord->kommun)
            )
            ->columns([
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
            ])
            ->defaultSort('postort')
            ->paginated([25, 50, 100, 200])
            ->defaultPaginationPageOption(25);
    }

    public function render(): View
    {
        return view('filament.resources.sweden-kommuners.kommun-postorter-page');
    }
}
