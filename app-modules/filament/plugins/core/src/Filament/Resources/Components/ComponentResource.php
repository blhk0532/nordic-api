<?php

namespace Cachet\Filament\Resources\Components;

use App\Models\User;
use Cachet\Enums\ComponentStatusEnum;
use Cachet\Filament\Resources\Components\Pages\CreateComponent;
use Cachet\Filament\Resources\Components\Pages\EditComponent;
use Cachet\Filament\Resources\Components\Pages\ListComponents;
use Cachet\Models\Component;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class ComponentResource extends Resource
{
    protected static ?string $model = Component::class;

    protected static bool $isScopedToTenant = false;

    protected static ?string $slug = 'campaigns';

    protected static string|\BackedEnum|null $navigationIcon = 'cachet-components';

    // protected static string|UnitEnum|null $navigationGroup = '';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columns(2)->schema([
                    TextInput::make('name')
                        ->label(__('cachet::component.form.name_label'))
                        ->required()
                        ->maxLength(255)
                        ->autocomplete(false),
                    ToggleButtons::make('status')
                        ->label(__('cachet::component.form.status_label'))
                        ->inline()
                        ->columnSpanFull()
                        ->default(ComponentStatusEnum::operational)
                        ->options(ComponentStatusEnum::class),
                    MarkdownEditor::make('description')
                        ->label(__('cachet::component.form.description_label'))
                        ->columnSpanFull(),
                    Select::make('component_group_id')
                        ->relationship('group', 'name')
                        ->searchable()
                        ->preload()
                        ->label(__('Project')),
                    Select::make('service_user_id')
                        ->options(fn () => User::query()->where('role', 'service')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->label(__('Tekniker')),
                    TextInput::make('link')
                        ->hidden()
                        ->label(__('cachet::component.form.link_label'))
                        ->url()
                        ->label(__('cachet::component.form.link_helper')),
                ]),

                Section::make()->columns(2)->schema([
                    KeyValue::make('meta')
                        ->hidden()
                        ->columnSpanFull(),
                    Toggle::make('enabled')
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('cachet::component.list.headers.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('cachet::component.list.headers.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('order')
                    ->label(__('cachet::component.list.headers.order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('group.name')
                    ->label(__('cachet::component.list.headers.group'))

                    ->sortable(),
                IconColumn::make('enabled')
                    ->label(__('cachet::component.list.headers.enabled'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_at')
                    ->label(__('cachet::component.list.headers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('cachet::component.list.headers.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('cachet::component.list.headers.deleted_at'))
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
                CreateAction::make()
                    ->label('')
                    ->icon('heroicon-c-plus-circle')
                    ->url(fn () => static::getUrl('create'))
                    ->color('success'),
            ])
            ->reorderable('order')
            ->defaultSort('order')
            ->emptyStateHeading(__('cachet::component.list.empty_state.heading'))
            ->emptyStateDescription(__('cachet::component.list.empty_state.description'));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComponents::route('/'),
            'create' => CreateComponent::route('/create'),
            'edit' => EditComponent::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return trans_choice('Kampanj', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('Kampanjer', 2);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Component::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        if ((int) static::getNavigationBadge() > 0) {
            return 'danger';
        }

        return 'success';
    }

    public static function getNavigationGroup(): ?string
    {
        $team = filament()->getTenant()?->name;
        $name = Str::ucwords($team);

        return $name ? ' TEAM | '.$name : 'TEAM | Administration';
        // return filament()->getTenant()?->name ? filament()->getTenant()?->name : 'Administration';
    }
}
