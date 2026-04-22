<?php

namespace App\Filament\Resources\AudioVoiceFlows\Pages;

use App\Filament\Resources\AudioVoiceFlows\AudioVoiceFlowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListAudioVoiceFlows extends ListRecords
{
    protected static string $resource = AudioVoiceFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Audio Files'),
            'active' => Tab::make('Active Queue')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'active'))
                ->icon('heroicon-m-play')
                ->badge(fn () => $this->getModel()::query()->where('status', 'active')->count()),
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'approved'))
                ->icon('heroicon-m-check-circle')
                ->badge(fn () => $this->getModel()::query()->where('status', 'approved')->count()),
            'draft' => Tab::make('Drafts')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'draft'))
                ->icon('heroicon-m-pencil-square')
                ->badge(fn () => $this->getModel()::query()->where('status', 'draft')->count()),
            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'archived'))
                ->icon('heroicon-m-archive-box')
                ->badge(fn () => $this->getModel()::query()->where('status', 'archived')->count()),
        ];
    }
}
