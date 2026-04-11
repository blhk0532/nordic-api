<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Messages\Tables;

use AdultDate\FilamentWirechat\Enums\MessageType;
use AdultDate\FilamentWirechat\Filament\Resources\Conversations\ConversationResource;
use AdultDate\FilamentWirechat\Models\Conversation;
use AdultDate\FilamentWirechat\Models\Message;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('conversation_id')
                    ->label('Conversation ID')
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => ConversationResource::getUrl('view', ['record' => $record->conversation_id]))
                    ->color('primary'),
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
                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->placeholder('Not deleted')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('conversation_id')
                    ->label('Conversation')
                    ->options(function () {
                        return Conversation::query()
                            ->orderBy('id', 'desc')
                            ->limit(100)
                            ->pluck('id', 'id')
                            ->mapWithKeys(fn ($id) => [$id => "Conversation #{$id}"])
                            ->toArray();
                    })
                    ->searchable(),
                SelectFilter::make('sender')
                    ->label('Sender')
                    ->options(function () {
                        return User::query()
                            ->whereIn('id', function ($query) {
                                $query->select('sendable_id')
                                    ->from((new Message)->getTable())
                                    ->where('sendable_type', User::class)
                                    ->distinct();
                            })
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'],
                        fn (Builder $query, $value): Builder => $query->where('sendable_id', $value)
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
