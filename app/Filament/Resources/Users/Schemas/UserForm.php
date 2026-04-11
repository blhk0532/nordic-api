<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AuthRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

use function filled;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->avatar()
                            ->imageEditor()
                            ->disk(config('filament-edit-profile.disk', 'public'))
                            ->visibility(config('filament-edit-profile.visibility', 'public'))
                            ->directory('avatars')
                            ->rules(['image', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:2048'])
                            ->hidden(false),
                        Textarea::make('notes')
                            ->label('Anteckningar')
                            ->nullable()
                            ->extraAttributes(['style' => 'overflow: auto;', 'class' => 'mt-6 pt-6'])
                            ->string(),
                        TextInput::make('name')
                            ->label('Användarnamn')
                            ->required()
                            ->string(),
                        Select::make('current_team_id')
                            ->label('Team')
                            ->relationship('currentTeam', 'name')
                            ->preload()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->searchable()
                            ->columnSpan(1),
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpan(1),
                        // TextInput::make('role')
                        //     ->label('Behörighet')
                        //     ->hidden()
                        //     ->visible(fn (?int $recordId) => Auth::user()?->role === 'super' ?? false)
                        //     ->required()
                        //     ->default('booking'),
                        TextInput::make('name_first')
                            ->label('Förnamn')
                            ->string(),
                        TextInput::make('name_last')
                            ->label('Efternamn')
                            ->string(),
                        TextInput::make('phone')
                            ->label('Telefonnummer'),
                        TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255),
                        TextInput::make('country')
                            ->label('Country')
                            ->maxLength(255),
                        TextInput::make('whatsapp')
                            ->label('Whatsapp')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->required()
                            ->label('E-postadress')
                            ->string()
                            ->unique('users', 'email', ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'The :attribute has already been registered.',
                            ])
                            ->email()
                            ->columnSpan(1),
                        Textarea::make('bio')
                            ->label('Bio')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('role')
                            ->label('Role')
                            ->visible(fn () => Auth::user()?->role === 'super')
                            ->options(collect(AuthRole::cases())->mapWithKeys(fn (AuthRole $role) => [
                                $role->value => $role->label(),
                            ])->toArray())
                            ->required()
                            ->default('user'),
                        DateTimePicker::make('email_verified_at')
                            ->required()
                            ->visible(fn (?int $recordId) => Auth::user()?->role === 'super' ?? false)
                            ->default(now()),
                        TextInput::make('password')
                            ->password()
                            ->confirmed()
                            ->label('Skapa Lösenord')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(6)
                            ->columnSpan(1),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->label('Bekräfta Lösenord')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->same('password')
                            ->dehydrated(false)
                            ->columnSpan(1),
                        TextInput::make('assigned_to_id')
                            ->label('Teamleader')
                            ->hidden()
                            ->required()
                            ->default(fn () => Auth::user()?->id),
                        TextInput::make('author_id')
                            ->label('Skapad av')
                            ->hidden()
                            ->default(fn () => Auth::user()?->id)
                            ->required(),
                        Toggle::make('status')
                            ->label('Användarstatus')
                            ->hidden()
                            ->helperText('')
                            ->default(true)
                            ->extraAttributes(['class' => 'mt-8 absolute'])
                            ->required(),
                    ]),
            ]);
    }
}
