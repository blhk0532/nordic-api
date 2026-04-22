<?php

namespace App\Filament\Resources\AudioVoiceFlows;

use App\Filament\Resources\AudioVoiceFlows\Pages\CreateAudioVoiceFlow;
use App\Filament\Resources\AudioVoiceFlows\Pages\EditAudioVoiceFlow;
use App\Filament\Resources\AudioVoiceFlows\Pages\ListAudioVoiceFlows;
use App\Filament\Resources\AudioVoiceFlows\Schemas\AudioVoiceFlowForm;
use App\Filament\Resources\AudioVoiceFlows\Tables\AudioVoiceFlowsTable;
use App\Models\AudioVoiceFlow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AudioVoiceFlowResource extends Resource
{
    protected static ?string $model = AudioVoiceFlow::class;

    protected static ?string $navigationLabel = 'Audio Voice Flow';

    protected static UnitEnum|string|null $navigationGroup = 'Dialers TELE';

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return 'Voice Script';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Voice Scripts';
    }

    public static function form(Schema $schema): Schema
    {
        return AudioVoiceFlowForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AudioVoiceFlowsTable::configure($table);
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
            'index' => ListAudioVoiceFlows::route('/'),
            'create' => CreateAudioVoiceFlow::route('/create'),
            'edit' => EditAudioVoiceFlow::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
