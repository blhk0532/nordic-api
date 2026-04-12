<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerCampaigns;

use App\Filament\Resources\DialerCampaigns\Pages\CreateDialerCampaign;
use App\Filament\Resources\DialerCampaigns\Pages\EditDialerCampaign;
use App\Filament\Resources\DialerCampaigns\Pages\ListDialerCampaigns;
use App\Filament\Resources\DialerCampaigns\Pages\ViewDialerCampaign;
use App\Filament\Resources\DialerCampaigns\RelationManagers\AttemptsRelationManager;
use App\Filament\Resources\DialerCampaigns\RelationManagers\LeadsRelationManager;
use App\Filament\Resources\DialerCampaigns\Schemas\DialerCampaignForm;
use App\Filament\Resources\DialerCampaigns\Schemas\DialerCampaignInfolist;
use App\Filament\Resources\DialerCampaigns\Tables\DialerCampaignsTable;
use App\Models\DialerCampaign;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DialerCampaignResource extends Resource
{
    protected static ?string $model = DialerCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoneArrowUpRight;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::PhoneArrowUpRight;

    protected static UnitEnum|string|null $navigationGroup = 'Dialers TELE';

    protected static ?string $navigationLabel = 'Dialer Campaigns';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

        public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        return null;
    }

    public static function form(Schema $schema): Schema
    {
        return DialerCampaignForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DialerCampaignInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DialerCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LeadsRelationManager::class,
            AttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDialerCampaigns::route('/'),
            'create' => CreateDialerCampaign::route('/create'),
            'view' => ViewDialerCampaign::route('/{record}'),
            'edit' => EditDialerCampaign::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->when($tenant !== null, fn (Builder $query) => $query->where('team_id', $tenant->id));
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->where('status', 'running')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
