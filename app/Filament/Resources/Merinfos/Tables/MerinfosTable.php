<?php

namespace App\Filament\Resources\Merinfos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables;
use Filament\Tables\Table;

class MerinfosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('short_uuid')
                    ->label('Short')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('givenNameOrFirstName')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('personalNumber'),
                Tables\Columns\TextColumn::make('pnr'),
                Tables\Columns\TextColumn::make('address')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\IconColumn::make('is_celebrity')->boolean(),
                Tables\Columns\IconColumn::make('has_company_engagement')->boolean(),
                Tables\Columns\TextColumn::make('number_plus_count'),
                Tables\Columns\TextColumn::make('phone_number'),
                Tables\Columns\TextColumn::make('url'),
                Tables\Columns\TextColumn::make('same_address_url'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
