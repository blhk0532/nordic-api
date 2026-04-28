<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Resources;

use CybertronianKelvin\Graper\Forms\Components\GrapesJsField;
use CybertronianKelvin\Graper\Models\GraperPage;
use CybertronianKelvin\Graper\Resources\GraperPageResource\Pages\CreateGraperPage;
use CybertronianKelvin\Graper\Resources\GraperPageResource\Pages\EditGraperPage;
use CybertronianKelvin\Graper\Resources\GraperPageResource\Pages\ListGraperPages;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Model;

class GraperPageResource extends Resource
{
    protected static ?string $model = GraperPage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pages';

    protected static bool $isScopedToTenant = false;

    protected static ?string $slug = 'graper-pages';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->default(fn () => auth()->user()->name)
                ->columnSpan(3),
            TextInput::make('slug')
                ->unique(ignoreRecord: true)
                ->columnSpan(3)
                ->default(fn () => auth()->user()->name)
                ->required(),
            Select::make('is_published')
                ->label('Published')
                ->options([true => 'Yes', false => 'No'])
                ->default('Yes')
                ->columnSpan(2)
                ->required(),
            DateTimePicker::make('published_at')
                ->default(fn () => now())
                ->displayFormat('d F Y')
                ->seconds(false)
                ->hidden()
                ->columnSpan(1),
            Action::make('viewPage')
                ->extraAttributes(['style' => 'position: relative;top: 27px;'])
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->url(fn (?Model $record) => $record ? route('graper.page.display', ['slug' => $record->slug]) : null)
                ->openUrlInNewTab()
                ->color('gray'),
            GrapesJsField::make('content')
                ->label('Page Editor')
                ->loadDefaultBlocks()
                ->minHeight('70vh')
                ->columnSpanFull(),
        ])->columns(9);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title'),
                TextColumn::make('slug'),
                IconColumn::make('is_published'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                  CreateAction::make()
                  ->label('Nya Sidor')
                  ->color('gray')
                  ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGraperPages::route('/'),
            'create' => CreateGraperPage::route('/create'),
            'edit' => EditGraperPage::route('/{record}/edit'),
        ];
    }
}
