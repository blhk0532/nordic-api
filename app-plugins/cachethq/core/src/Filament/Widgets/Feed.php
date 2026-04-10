<?php

namespace Cachet\Filament\Widgets;

use App\Models\User;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\MentionProvider;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Throwable;

class Feed extends Widget implements HasForms
{
    use CanPoll;
    use InteractsWithForms;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'cachet::filament.widgets.feed';

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 10000;

    protected function getViewData(): array
    {
        return [
            'items' => $this->getFeed(),
            'noItems' => Blade::render($this->getEmptyBlock()),
            'heading' => __('yolo'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columns(2)->schema([
                    TextInput::make('name')
                        ->default(now())
                        ->label('Dashboard Title'),
                    RichEditor::make('about')
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

                                ToolbarButtonGroup::make('Code', [
                                    'code', 'codeBlock',
                                ])->icon('heroicon-o-code-bracket'),
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
                                    'customBlocks', 'mergeTags', 'details', 'grid',
                                ])->icon('heroicon-o-cog-6-tooth'),
                            ],

                            // Utilities
                            [
                                ToolbarButtonGroup::make('Utilities', [
                                    'clearFormatting', 'undo', 'redo',
                                ])->icon('heroicon-o-arrow-path'),
                            ],
                        ])
                        ->mentions([
                            MentionProvider::make('@')
                                ->getSearchResultsUsing(fn (string $search): array => User::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->orderBy('name')
                                    ->limit(10)
                                    ->pluck('name', 'id')
                                    ->all())
                                ->getLabelsUsing(fn (array $ids): array => User::query()
                                    ->whereIn('id', $ids)
                                    ->pluck('name', 'id')
                                    ->all()),
                        ])
                        ->fileAttachmentsDisk('local')
                        ->fileAttachmentsDirectory('attachments')
                        ->fileAttachmentsVisibility('public')
                        ->customTextColors()
                        ->resizableImages()
                        ->mergeTags([
                            'name',
                            'today',
                        ])
                        ->floatingToolbars([
                            'paragraph' => [
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'subscript',
                                'superscript',
                            ],
                            'heading' => [
                                'h1',
                                'h2',
                                'h3',
                            ],
                            'table' => [
                                'tableAddColumnBefore',
                                'tableAddColumnAfter',
                                'tableDeleteColumn',
                                'tableAddRowBefore',
                                'tableAddRowAfter',
                                'tableDeleteRow',
                                'tableMergeCells',
                                'tableSplitCell',
                                'tableToggleHeaderRow',
                                'tableToggleHeaderCell',
                                'tableDelete',
                            ],
                        ])
                        ->label('Dashboard Message')
                        ->default('')
                        ->columnSpanFull(),
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

    public function renderWidget()
    {
        return view('cachet::filament.widgets.feed', [
            'items' => $this->getFeed(),
            'noItems' => Blade::render($this->getEmptyBlock()),
            'heading' => __('cachet::cachet.feed.section_heading'),
        ]);
    }

    /**
     * Get the feed from the cache or fetch it fresh.
     */
    protected function getFeed(): array
    {
        //   $feedUri = config('cachet.feed.uri');
        $feedUri = [];

        if (blank($feedUri)) {
            return [];
        }

        return Cache::flexible('nordic-feed', [
            60 * 15,
            60 * 60,
        ], fn () => $this->fetchFeed(''));
    }

    /**
     * Fetch the data from the given RSS feed.
     */
    protected function fetchFeed(string $uri, int $maxPosts = 5): array
    {
        try {
            $response = Http::get($uri);

            $xml = simplexml_load_string($response->body());

            $posts = [];

            $feedItems = $xml->channel->item ?? $xml->entry ?? [];
            $feedIndex = 0;

            foreach ($feedItems as $item) {
                if ($feedIndex >= $maxPosts) {
                    break;
                }

                $posts[] = [
                    'title' => (string) ($item->title ?? ''),
                    'link' => Uri::of((string) ($item->link ?? ''))->withQuery([
                        'utm_source' => 'cachet',
                        'utm_medium' => 'installation',
                        'utm_campaign' => 'dashboard',
                    ])->toString(),
                    'description' => Str::of($item->description ?? $item->summary ?? '')->limit(preserveWords: true),
                    'date' => Carbon::parse((string) ($item->pubDate ?? $item->updated ?? '')),
                ];

                $feedIndex++;
            }

            return $posts;
        } catch (Throwable $e) {
            return [];
        }
    }
}
