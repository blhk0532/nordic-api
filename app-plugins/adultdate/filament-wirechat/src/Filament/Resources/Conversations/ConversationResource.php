<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Conversations;

use AdultDate\FilamentWirechat\Filament\Resources\Conversations\Pages\CreateConversation;
use AdultDate\FilamentWirechat\Filament\Resources\Conversations\Pages\EditConversation;
use AdultDate\FilamentWirechat\Filament\Resources\Conversations\Pages\ListConversations;
use AdultDate\FilamentWirechat\Filament\Resources\Conversations\Pages\ViewConversation;
use AdultDate\FilamentWirechat\Filament\Resources\Conversations\Schemas\ConversationForm;
use AdultDate\FilamentWirechat\Filament\Resources\Conversations\Schemas\ConversationInfolist;
use AdultDate\FilamentWirechat\Filament\Resources\Conversations\Tables\ConversationsTable;
use AdultDate\FilamentWirechat\Models\Conversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static UnitEnum|string|null $navigationGroup = 'Chats';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Conversations';

    protected static ?string $modelLabel = 'Conversation';

    protected static ?string $pluralModelLabel = 'Conversations';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        return true; // Allow all authenticated users to view
    }

    public static function form(Schema $schema): Schema
    {
        return ConversationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConversationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConversationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConversations::route('/'),
            'create' => CreateConversation::route('/create'),
            'view' => ViewConversation::route('/{record}'),
            'edit' => EditConversation::route('/{record}/edit'),
        ];
    }
}
