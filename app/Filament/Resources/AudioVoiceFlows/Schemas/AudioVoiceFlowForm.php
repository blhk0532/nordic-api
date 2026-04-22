<?php

namespace App\Filament\Resources\AudioVoiceFlows\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class AudioVoiceFlowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audio Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Voice Script Name')
                                ->required()
                                ->columnSpan(1),
                            Select::make('status')
                                ->options([
                                    'draft' => 'Draft',
                                    'approved' => 'Approved',
                                    'active' => 'Active',
                                    'archived' => 'Archived',
                                ])
                                ->default('draft')
                                ->required()
                                ->columnSpan(1),
                        ]),
                        FileUpload::make('filename')
                            ->label('Audio File')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/mp3', 'audio/*'])
                            ->disk('local')
                            ->directory('audio-voice-flow')
                            ->visibility('private')
                            ->columnSpanFull()
                            ->required(),
                        Textarea::make('description')
                            ->label('Description / Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Queue & Organization')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('priority')
                                ->label('Queue Priority')
                                ->type('number')
                                ->default(0)
                                ->columnSpan(1),
                            TextInput::make('duration')
                                ->label('Duration (seconds)')
                                ->type('number')
                                ->helperText('Audio length in seconds')
                                ->columnSpan(1),
                            TextInput::make('play_count')
                                ->label('Play Count')
                                ->type('number')
                                ->default(0)
                                ->disabled()
                                ->columnSpan(1),
                        ]),
                        TagsInput::make('tags')
                            ->label('Tags / Categories')
                            ->placeholder('Add tags (e.g., telemarketing, sales)')
                            ->columnSpanFull(),
                    ]),

                Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }
}
