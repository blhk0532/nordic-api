<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerLeads;

use App\Filament\Resources\DialerLeads\Pages\CreateDialerLead;
use App\Filament\Resources\DialerLeads\Pages\EditDialerLead;
use App\Filament\Resources\DialerLeads\Pages\ListDialerLeads;
use App\Filament\Resources\DialerLeads\Pages\ViewDialerLead;
use App\Filament\Resources\DialerLeads\RelationManagers\AttemptsRelationManager;
use App\Filament\Resources\DialerLeads\Schemas\DialerLeadForm;
use App\Filament\Resources\DialerLeads\Schemas\DialerLeadInfolist;
use App\Filament\Resources\DialerLeads\Tables\DialerLeadsTable;
use App\Models\DialerLead;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DialerLeadResource extends Resource
{
    protected static ?string $model = DialerLead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Phone;

    protected static UnitEnum|string|null $navigationGroup = 'Dialers TELE';

    protected static ?string $navigationLabel = 'Dialer Leads';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DialerLeadForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DialerLeadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DialerLeadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDialerLeads::route('/'),
            'create' => CreateDialerLead::route('/create'),
            'view' => ViewDialerLead::route('/{record}'),
            'edit' => EditDialerLead::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->with(['campaign'])
            ->when($tenant !== null, fn (Builder $query) => $query->where('team_id', $tenant->id));
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }
}
