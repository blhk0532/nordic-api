<?php

namespace Cachet\Filament\Resources\ComponentGroups;

use Cachet\Enums\ComponentGroupVisibilityEnum;
use Cachet\Enums\ResourceVisibilityEnum;
use Cachet\Filament\Resources\ComponentGroups\Pages\CreateComponentGroup;
use Cachet\Filament\Resources\ComponentGroups\Pages\EditComponentGroup;
use Cachet\Filament\Resources\ComponentGroups\Pages\ListComponentGroups;
use Cachet\Filament\Resources\Components\RelationManagers\ComponentsRelationManager;
use Cachet\Models\ComponentGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

// use Wallacemartinss\FilamentIconPicker\Enums\Heroicons;
// use Wallacemartinss\FilamentIconPicker\Enums\SimpleIcons;

class ComponentGroupResource extends Resource
{
    protected static ?string $model = ComponentGroup::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static bool $isScopedToTenant = false;

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = '';

    protected static bool $isDiscovered = true;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columns(2)->schema([
                    TextInput::make('name')
                        ->label(__('cachet::component_group.form.name_label'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->autocomplete(false),
                    ToggleButtons::make('visible')
                        ->label(__('cachet::component_group.form.visible_label'))
                        ->inline()
                        ->hidden()
                        ->options(ResourceVisibilityEnum::class)
                        ->default(ResourceVisibilityEnum::guest)
                        ->live()
                        ->required()
                        ->columnSpanFull(),
                    Select::make('team_id')
                        ->label(__('Team'))
                        ->relationship('team', 'name')
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->visible(fn (Get $get): bool => $get('visible') === ResourceVisibilityEnum::team || $get('visible') === ResourceVisibilityEnum::team->value),
                    ToggleButtons::make('collapsed')
                        ->label(__('cachet::component_group.form.collapsed_label'))
                        ->required()
                        ->hidden()
                        ->inline()
                        ->options(ComponentGroupVisibilityEnum::class)
                        ->default(ComponentGroupVisibilityEnum::expanded)
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('cachet::component_group.list.headers.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('visible')
                    ->label(__('cachet::component_group.list.headers.visible'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('collapsed')
                    ->label(__('cachet::component_group.list.headers.collapsed'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('cachet::component_group.list.headers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('cachet::component_group.list.headers.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order')
            ->defaultSort('order')
            ->emptyStateHeading(__('cachet::component_group.list.empty_state.heading'))
            ->emptyStateDescription(__('cachet::component_group.list.empty_state.description'));
    }

    public static function getRelations(): array
    {
        return [
            ComponentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComponentGroups::route('/'),
            'create' => CreateComponentGroup::route('/create'),
            'edit' => EditComponentGroup::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return trans_choice('cachet::component_group.resource_label', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('cachet::component_group.resource_label', 2);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) ComponentGroup::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        if ((int) static::getNavigationBadge() > 0) {
            return 'info';
        }

        return 'gray';
    }

    public static function getNavigationGroup(): ?string
    {
        $team = filament()->getTenant()?->name;
        $name = Str::ucwords($team);

        return $name ? ' TEAM | '.$name : 'TEAM | Administration';
        // return filament()->getTenant()?->name ? filament()->getTenant()?->name : 'Administration';
    }
}
