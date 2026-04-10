<?php

namespace Cachet\Filament\Pages\Settings;

use Cachet\Settings\AppSettings;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

use function __;

class ManageCachet extends SettingsPage
{
    protected static string $settings = AppSettings::class;

    protected static ?string $title = '';

    public static function getNavigationGroup(): ?string
    {
        return __('cachet::navigation.settings.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('Application');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columns(2)->schema([
                    MarkdownEditor::make('name')
                        ->default(now())
                        ->label('Dashboard Title'),
                    RichEditor::make('about')
                        ->label('Dashboard Message')
                        ->extraAttributes(['data-tiptap-mentionable' => 'true', 'class' => 'min-h-[200px]'])
                        ->extraFieldWrapperAttributes(['data-tiptap-mentionable' => 'true', 'class' => 'min-h-[200px]'])
                        ->toolbarButtons([
                            // Headings
                            [
                                ToolbarButtonGroup::make('Headings', [
                                    'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                                ])->icon('fi-o-heading'),
                            ],

                            // Text alignment
                            [
                                ToolbarButtonGroup::make('Alignment', [
                                    'alignStart', 'alignCenter', 'alignEnd', 'alignJustify',
                                ])->icon('heroicon-o-bars-3-bottom-left'),
                            ],

                            // Text formatting
                            [
                                ToolbarButtonGroup::make('Text Style', [
                                    'bold', 'italic', 'underline', 'strike',
                                ])->icon('heroicon-o-bold'),

                                ToolbarButtonGroup::make('Advanced Text', [
                                    'subscript', 'superscript', 'small', 'lead',
                                ]),
                            ],

                            // Colors & highlights
                            [
                                ToolbarButtonGroup::make('Colors', [
                                    'textColor', 'highlight',
                                ])->icon('heroicon-o-swatch'),
                            ],

                            // Structure
                            [
                                ToolbarButtonGroup::make('Structure', [
                                    'paragraph', 'blockquote', 'horizontalRule',
                                ])->icon('heroicon-o-document-text'),
                            ],

                            // Lists & content blocks
                            [
                                ToolbarButtonGroup::make('Lists', [
                                    'bulletList', 'orderedList',
                                ])->icon('heroicon-o-list-bullet'),

                            ],

                            // Media & embeds
                            [
                                ToolbarButtonGroup::make('Media', [
                                    'link', 'table', 'attachFiles',
                                ])->icon('heroicon-o-paper-clip'),
                            ],

                            // Advanced features
                            [
                                ToolbarButtonGroup::make('Advanced', [
                                    'customBlocks', 'mergeTags', 'details', 'grid',  'code', 'codeBlock',
                                ])->icon('heroicon-o-code-bracket'),
                            ],

                            // Utilities
                            [
                                ToolbarButtonGroup::make('Utilities', [
                                    'clearFormatting', 'undo', 'redo',
                                ])->icon('heroicon-o-arrow-path'),
                            ],
                        ])
                        ->columnSpan('1/2'),
                ]),

                Section::make()->columns(3)->schema([
                    TextInput::make('incident_days')
                        ->numeric()
                        ->label(__('cachet::settings.manage_cachet.incident_days_label'))
                        ->minValue(1)
                        ->maxValue(3650)
                        ->step(1),

                    TextInput::make('major_outage_threshold')
                        ->numeric()
                        ->label(__('cachet::settings.manage_cachet.major_outage_threshold_label'))
                        ->minValue(1)
                        ->maxValue(100)
                        ->step(1)
                        ->suffix('%'),

                    TextInput::make('refresh_rate')
                        ->numeric()
                        ->label(__('cachet::settings.manage_cachet.refresh_rate_label'))
                        ->minValue(0)
                        ->nullable()
                        ->step(1)
                        ->suffix(__('cachet::settings.manage_cachet.refresh_rate_label_input_suffix_seconds')),

                    Grid::make(1)
                        ->schema([
                            Toggle::make('recent_incidents_only')
                                ->label(__('cachet::settings.manage_cachet.toggles.recent_incidents_only'))
                                ->reactive(),
                            TextInput::make('recent_incidents_days')
                                ->numeric()
                                ->label(__('cachet::settings.manage_cachet.toggles.recent_incidents_days'))
                                ->minValue(0)
                                ->nullable()
                                ->step(1)
                                ->suffix(__('cachet::settings.manage_cachet.recent_incidents_days_suffix_days'))
                                ->hidden(fn (Get $get) => $get('recent_incidents_only') !== true),
                        ]),
                ]),

                Section::make(__('cachet::settings.manage_cachet.display_settings_title'))
                    ->label('')
                    ->schema([
                        Toggle::make('dashboard_login_link')
                            ->label(__('cachet::settings.manage_cachet.toggles.show_dashboard_link')),
                        Toggle::make('show_support')
                            ->label(__('Support Nordic Digital')),
                        Toggle::make('display_graphs')
                            ->label(__('cachet::settings.manage_cachet.toggles.display_graphs')),
                        Toggle::make('enable_external_dependencies')
                            ->label(__('cachet::settings.manage_cachet.toggles.enable_external_dependencies')),
                        Toggle::make('only_disrupted_days')
                            ->label(__('Only Show Active Days')),
                    ]),
            ]);
    }
}
