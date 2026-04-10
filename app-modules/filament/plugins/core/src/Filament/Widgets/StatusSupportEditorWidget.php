<?php

declare(strict_types=1);

namespace Cachet\Filament\Widgets;

use App\Models\User;
use Cachet\Settings\AppSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\MentionProvider;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class StatusSupportEditorWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    /**
     * Avoid dynamic property creation on the widget instance (deprecated in PHP 8.4+).
     * These are populated via the form state.
     */
    public ?string $name = null;

    /**
     * @var string|array|null
     */
    public $about = null;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = true;

    protected static ?int $sort = 1;

    protected string $view = 'cachet::filament.widgets.status-about-widget';

    public function mount(): void
    {
        $settings = app(AppSettings::class);
        $this->form->fill([
            'name' => $settings->name,
            'about' => $settings->about,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columns(2)->schema([
                    TextInput::make('name')
                        ->label('Dashboard Title')
                        ->live(),
                    RichEditor::make('about')
                        ->live()
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
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        $settings = app(AppSettings::class);
        $settings->name = $state['name'] ?? 'Cachet';
        $settings->about = $state['about'] ?? '';
        $settings->save();

        $this->form->fill([
            'name' => $settings->name,
            'about' => $settings->about,
        ]);

        Notification::make()
            ->success()
            ->title('Dashboard settings saved')
            ->body('The dashboard title and message have been updated.')
            ->send();
    }

    protected function getViewData(): array
    {
        return [
            'supportingHeading' => ' ',
        ];
    }
}
