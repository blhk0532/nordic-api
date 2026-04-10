<?php

namespace Cachet\Filament\Resources\Components\RelationManagers;

use Cachet\Enums\ComponentStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ComponentsRelationManager extends RelationManager
{
    protected static string $relationship = 'components';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Components');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('cachet::component.form.name_label'))
                    ->required()
                    ->maxLength(255),
                ToggleButtons::make('status')
                    ->label(__('cachet::component.form.status_label'))
                    ->inline()
                    ->columnSpanFull()
                    ->default('operational')
                    ->options(ComponentStatusEnum::class)
                    ->required(),
                Select::make('group_id')
                    ->label(__('cachet::component.form.campaign_group_label'))
                    ->relationship('group', 'name')
                    ->preload()
                    ->searchable()
                    ->nullable(),
                MarkdownEditor::make('description')
                    ->label(__('cachet::component.form.description_label'))
                    ->columnSpanFull(),
                TextInput::make('link')
                    ->label(__('cachet::component.form.link_label'))
                    ->url()
                    ->helperText(__('cachet::component.form.link_helper')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->heading(__('Campaings'))
            ->modelLabel(trans_choice('Campaings', 1))
            ->pluralModelLabel(trans_choice('Campaings', 2))
            ->columns([
                TextColumn::make('name')
                    ->label(__('cachet::component.list.headers.name')),
                TextColumn::make('status')
                    ->label(__('cachet::component.list.headers.status'))
                    ->badge()
                    ->sortable(),
                IconColumn::make('enabled')
                    ->label(__('cachet::component.list.headers.enabled'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                //    CreateAction::make(),
            ])
            ->reorderable('order')
            ->defaultSort('order');
    }
}
