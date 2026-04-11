<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Messages;

use AdultDate\FilamentWirechat\Filament\Resources\Messages\Pages\CreateMessage;
use AdultDate\FilamentWirechat\Filament\Resources\Messages\Pages\EditMessage;
use AdultDate\FilamentWirechat\Filament\Resources\Messages\Pages\ListMessages;
use AdultDate\FilamentWirechat\Filament\Resources\Messages\Pages\ViewMessage;
use AdultDate\FilamentWirechat\Filament\Resources\Messages\Schemas\MessageForm;
use AdultDate\FilamentWirechat\Filament\Resources\Messages\Schemas\MessageInfolist;
use AdultDate\FilamentWirechat\Filament\Resources\Messages\Tables\MessagesTable;
use AdultDate\FilamentWirechat\Models\Message;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static UnitEnum|string|null $navigationGroup = 'Chats';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationLabel = 'Messages';

    protected static ?string $modelLabel = 'Message';

    protected static ?string $pluralModelLabel = 'Messages';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return true; // Allow all authenticated users to view
    }

    public static function form(Schema $schema): Schema
    {
        return MessageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
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
            'index' => ListMessages::route('/'),
            'create' => CreateMessage::route('/create'),
            'view' => ViewMessage::route('/{record}'),
            'edit' => EditMessage::route('/{record}/edit'),
        ];
    }

    /**
     * Get the query builder for global search.
     * Only search messages in conversations where the current user is a participant.
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return self::getModel()::query()->whereRaw('1 = 0'); // Return empty query if not authenticated
        }

        return self::getModel()::query()
            ->whereHas('conversation.participants', function ($query) use ($user) {
                $query->where('participantable_id', $user->getKey())
                    ->where('participantable_type', $user->getMorphClass());
            });
    }

    /**
     * Get the attributes that should be globally searchable.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['body'];
    }
}
