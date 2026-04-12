<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('notification_template_id')
                            ->relationship('template', 'name')
                            ->required()
                            ->searchable(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        Select::make('channel')
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'slack' => 'Slack',
                                'discord' => 'Discord',
                                'push' => 'Push Notification',
                            ])
                            ->required(),
                        TextInput::make('subject')
                            ->maxLength(255),
                        Textarea::make('content')
                            ->rows(10)
                            ->readonly(),
                        KeyValue::make('data')
                            ->keyLabel('Key')
                            ->valueLabel('Value'),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'sent' => 'Sent',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                        DateTimePicker::make('scheduled_at')
                            ->readonly(),
                        DateTimePicker::make('sent_at')
                            ->readonly(),
                        Textarea::make('error')
                            ->rows(3)
                            ->readonly()
                            ->visible(fn ($record) => $record && $record->status === 'failed'),
                    ]),
            ]);
    }
}
