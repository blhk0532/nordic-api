<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Conversations\RelationManagers;

use AdultDate\FilamentWirechat\Enums\MessageType;
use AdultDate\FilamentWirechat\Models\Message;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Messages';

    protected static ?string $recordTitleAttribute = 'body';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('body')
                    ->label('Message')
                    ->limit(100)
                    ->tooltip(fn ($record) => $record->body)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof MessageType ? $state : MessageType::tryFrom($state ?? '')) {
                        MessageType::TEXT => 'gray',
                        MessageType::ATTACHMENT => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sendable_type')
                    ->label('Sender Type')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                TextColumn::make('sendable.name')
                    ->label('Sender')
                    ->default('Unknown')
                    ->searchable(),
                TextColumn::make('reply.body')
                    ->label('Reply To')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->reply?->body)
                    ->default('Not a reply')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kept_at')
                    ->label('Kept At')
                    ->dateTime()
                    ->placeholder('Not kept')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('sender')
                    ->label('Sender')
                    ->options(function () {
                        $conversationId = $this->getOwnerRecord()->id;

                        return User::query()
                            ->whereIn('id', function ($query) use ($conversationId) {
                                $query->select('sendable_id')
                                    ->from((new Message)->getTable())
                                    ->where('sendable_type', User::class)
                                    ->where('conversation_id', $conversationId)
                                    ->distinct();
                            })
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($query, $value) => $query->where('sendable_id', $value)
                            ->where('sendable_type', User::class)
                    ))
                    ->searchable(),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        MessageType::TEXT->value => 'Text',
                        MessageType::ATTACHMENT->value => 'Attachment',
                    ]),
            ])
            ->headerActions([
                // CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
