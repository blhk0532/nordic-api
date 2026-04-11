<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Livewire\Chats;

use AdultDate\FilamentWirechat\Filament\Pages\ChatPage;
use AdultDate\FilamentWirechat\Models\Conversation;
use Adultdate\Wirechat\Helpers\MorphClassResolver;
use Adultdate\Wirechat\Livewire\Concerns\HasPanel;
use Adultdate\Wirechat\Livewire\Concerns\Widget;
use Exception;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Chats Component
 *
 * Handles chat conversations, search, and real-time updates.
 *
 * @property Authenticatable|null $auth
 */
class Chats extends Component
{
    use HasPanel;
    use Widget;

    /**
     * The search query.
     *
     * @var mixed
     */
    public $search;

    /**
     * The list of conversations.
     *
     * @var Collection|array
     */
    public $conversations = [];

    /**
     * Features
     */
    #[Locked]
    public ?bool $createChatAction = null;

    #[Locked]
    public ?bool $chatsSearch = null;

    #[Locked]
    public ?bool $redirectToHomeAction = null;

    #[Locked]
    public ?string $heading = '';

    /**
     * Indicates if more conversations can be loaded.
     */
    public bool $canLoadMore = false;

    /**
     * The current page for pagination.
     *
     * @var int
     */
    public $page = 1;

    /**
     * The ID of the selected conversation.
     *
     * @var mixed
     */
    public $selectedConversationId;

    /**
     * Returns an array of event listeners.
     *
     * @return array
     */
    public function getListeners()
    {
        $user = $this->resolveAuthUser();
        $encodedType = MorphClassResolver::encode($user instanceof Model ? $user->getMorphClass() : null);
        $userId = $user?->getKey();

        $listeners = [
            'refresh' => '$refresh',
            'hardRefresh',
        ];

        if ($this->panel() === null) {
            Log::warning('Wirechat:No panels registered in Chat Component');
        } else {
            $panelId = $this->panel()->getId();
            // Construct the channel name using the encoded type and user ID.
            $channelName = "$panelId.participant.$encodedType.$userId";
            $listeners["echo-private:{$channelName},.Wirechat\\Wirechat\\Events\\NotifyParticipant"] = 'refreshComponent';
        }

        return $listeners;
    }

    /**
     * Forces the conversation list to reset as if it was newly opened.
     *
     * @return void
     */
    public function hardRefresh()
    {
        $this->conversations = collect();
        $this->reset(['page', 'canLoadMore']);
    }

    /**
     * Refreshes the chats by resetting the conversation list and pagination.
     *
     * @return void
     */
    #[On('refresh-chats')]
    public function refreshChats()
    {
        $this->conversations = collect();
        $this->reset(['page', 'canLoadMore']);
    }

    /**
     * Handle the 'chat-deleted' event.
     *
     * @param  mixed  $conversationId  The ID of the deleted conversation.
     * @return void
     */
    #[On('chat-deleted')]
    public function chatDeleted($conversationId)
    {
        $this->conversations = $this->conversations->reject(function ($conversation) use ($conversationId) {
            return $conversation->id === $conversationId;
        });
    }

    /**
     * Handle the 'chat-exited' event.
     *
     * @param  mixed  $conversationId  The ID of the exited conversation.
     * @return void
     */
    #[On('chat-exited')]
    public function chatExited($conversationId)
    {
        $this->conversations = $this->conversations->reject(function ($conversation) use ($conversationId) {
            return $conversation->id === $conversationId;
        });
    }

    /**
     * Refreshes the component if the event's conversation ID does not match the selected conversation.
     *
     * @param  array  $event  Event data containing message and conversation details.
     * @return void
     */
    public function refreshComponent($event)
    {
        if ($event['message']['conversation_id'] !== $this->selectedConversationId) {
            $this->dispatch('refresh')->self();
            // Dispatch event to update unread count badge
            $this->dispatch('refresh-unread-count');
        }
    }

    /**
     * Loads more conversations if available.
     *
     * @return void|null
     */
    public function loadMore()
    {
        // Check if no more conversations are available.
        if (! $this->canLoadMore) {
            return null;
        }

        // Load the next page.
        $this->page++;
    }

    /**
     * Resets conversations and pagination when the search query is updated.
     *
     * @param  mixed  $value  The new search query.
     * @return void
     */
    public function updatedSearch($value)
    {
        $this->conversations = []; // Clear previous results when a new search is made.
        $this->reset(['page', 'canLoadMore']);
    }

    /**
     * Eager loads additional conversation relationships.
     *
     * @return void
     */
    public function hydrateConversations()
    {
        $authUser = $this->resolveAuthUser();

        if (! $authUser instanceof Model && ! $authUser instanceof Authenticatable) {
            return;
        }

        $this->conversations->map(function ($conversation) use ($authUser) {
            // Only load participants manually if not a group
            if (! $conversation->isGroup()) {
                $participants = $conversation->participants()->select('id', 'participantable_id', 'participantable_type', 'conversation_id', 'conversation_read_at')->with(['participantable', 'actions'])->get();

                $conversation->setRelation('participants', $participants);

                // Set peer and auth participants
                $conversation->auth_participant = $conversation->participant($authUser);
                $conversation->peer_participant = $conversation->peerParticipant(reference: $authUser);
            }

            return $conversation->loadMissing([
                'lastMessage',
                'group.cover' => fn ($query) => $query->select('id', 'url', 'attachable_type', 'attachable_id', 'file_path'),
            ]);
        });
    }

    /**
     * Returns the authenticated user.
     *
     * @return Authenticatable|null
     */
    #[Computed]
    public function auth()
    {
        return Auth::user();
    }

    private function resolveAuthUser(): Model|Authenticatable|null
    {
        $user = $this->auth;

        if ($user instanceof Model || $user instanceof Authenticatable) {
            return $user;
        }

        $freshUser = Auth::user();

        if ($freshUser instanceof Model || $freshUser instanceof Authenticatable) {
            return $freshUser;
        }

        return null;
    }

    /**
     * Mounts the component and initializes conversations.
     *
     * @return void
     */
    public function mount()
    {

        abort_unless(Auth::check(), 401);
        $conversation = request()->route('conversation');
        $this->selectedConversationId = $conversation ? $conversation->id : request()->conversation;
        $this->conversations = collect();

    }

    /**
     * Get the chat route for a conversation.
     * For Filament pages, generates a Filament page URL.
     * For standalone wirechat, uses panel's chatRoute.
     *
     * @param  mixed  $conversation  The conversation or conversation ID
     * @param  bool  $absolute  Whether to return an absolute URL
     */
    public function chatRoute($conversation, bool $absolute = true): string
    {
        $conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;

        // Prefer current Filament panel URL generation (tenant-aware) when available.
        try {
            if (class_exists(Filament::class) && class_exists(ChatPage::class)) {
                $filamentPanel = Filament::getCurrentPanel();

                if ($filamentPanel !== null) {
                    $tenant = Filament::getTenant();

                    return ChatPage::getUrl(
                        ['conversation' => $conversationId],
                        $absolute,
                        $filamentPanel->getId(),
                        $tenant instanceof Model ? $tenant : null
                    );
                }
            }
        } catch (Exception $e) {
            // Continue to fallback resolution.
        }

        // Check if we're in widget mode - if so, we don't need a route
        if ($this->isWidget() === true) {
            // Widget mode doesn't use routes, but we still need to return something
            // This shouldn't be called in widget mode, but just in case
            $path = '/admin/chats/'.$conversationId;

            return $absolute ? url($path) : $path;
        }

        // Fallback to standalone wirechat panel route.
        if ($this->panel()) {
            return $this->panel()->chatRoute($conversation, $absolute);
        }

        // Ultimate fallback
        $path = '/admin/chats/'.$conversationId;

        return $absolute ? url($path) : $path;
    }

    /**
     * Get the dashboard route URL.
     * Priority order:
     * 1. Panel's homeButtonUrl() method
     * 2. Config file (filament-wirechat.dashboard_route)
     * 3. Default Filament panel
     */
    public function dashboardRoute(): string
    {
        // First, check if panel has a homeButtonUrl set
        $panel = $this->panel();
        if ($panel && method_exists($panel, 'getHomeButtonUrl')) {
            $panelHomeUrl = $panel->getHomeButtonUrl();
            if ($panelHomeUrl !== null) {
                return $this->resolveDashboardRoute($panelHomeUrl);
            }
        }

        // Second, check config file
        $configRoute = config('filament-wirechat.dashboard_route', 'default');
        if ($configRoute !== 'default' && $configRoute !== null) {
            return $this->resolveDashboardRoute($configRoute);
        }

        // Third, default to default Filament panel
        return $this->getDefaultFilamentPanelUrl();
    }

    /**
     * Loads conversations and renders the view.
     *
     * @return View
     */
    public function render()
    {
        $this->loadConversations();

        $this->initialize();

        return view('wirechat::livewire.chats.chats');
    }

    /**
     * Loads conversations based on the current page and search filters.
     * Applies search filters and updates the conversations collection.
     *
     * @return void
     */
    protected function loadConversations()
    {
        $authUser = $this->resolveAuthUser();

        if (! $authUser instanceof Model && ! $authUser instanceof Authenticatable) {
            $this->canLoadMore = false;

            return;
        }

        $perPage = 10;
        $offset = ($this->page - 1) * $perPage;

        $additionalConversations = $authUser->conversations()
            ->with([
                'lastMessage.participant.participantable',
                'group.cover' => fn ($query) => $query->select('id', 'url', 'attachable_type', 'attachable_id', 'file_path'),
            ])
            ->when(mb_trim($this->search ?? '') !== '', fn ($query) => $this->applySearchConditions($query))
            ->when(mb_trim($this->search ?? '') === '', function ($query) {
                /** @phpstan-ignore-next-line */
                return $query->withoutDeleted()->withoutBlanks();
            })
            ->latest('updated_at')
            ->skip($offset)
            ->take($perPage)
            ->get();

        // Set participants manually where needed
        $additionalConversations->each(function ($conversation) use ($authUser) {
            if ($conversation->isPrivate() || $conversation->isSelf()) {
                // Manually load participants (only 2 expected in private/self)
                $participants = $conversation->participants()->select('id', 'participantable_id', 'participantable_type', 'conversation_id', 'conversation_read_at')->with('participantable')->get();
                $conversation->setRelation('participants', $participants);

                // Set peer and auth participants
                $conversation->auth_participant = $conversation->participant($authUser);
                $conversation->peer_participant = $conversation->peerParticipant($authUser);
            }
        });

        $this->canLoadMore = $additionalConversations->count() === $perPage;

        $this->conversations = collect($this->conversations)
            ->concat($additionalConversations)
            ->unique('id')
            ->sortByDesc('updated_at')
            ->values();
    }

    /**
     * Applies search conditions to the conversations query.
     *
     * @param  Builder  $query  The query builder instance.
     */
    protected function applySearchConditions($query): Builder
    {
        $searchableFields = $this->panel()->getSearchableAttributes();
        $groupSearchableFields = ['name', 'description'];
        $columnCache = [];

        // Use withDeleted to reverse withoutDeleted in order to make deleted chats appear in search.
        /** @phpstan-ignore-next-line */
        return $query->withDeleted()->where(function ($query) use ($searchableFields, $groupSearchableFields, &$columnCache) {
            // Search in participants' participantable fields.
            $query->whereHas('participants', function ($subquery) use ($searchableFields, &$columnCache) {
                $subquery->whereHas('participantable', function ($query2) use ($searchableFields, &$columnCache) {
                    $query2->where(function ($query3) use ($searchableFields, &$columnCache) {
                        $table = $query3->getModel()->getTable();
                        foreach ($searchableFields as $field) {
                            if ($this->columnExists($table, $field, $columnCache)) {
                                $query3->orWhere($field, 'LIKE', '%'.$this->search.'%');
                            }
                        }
                    });
                });
            });

            // Search in group fields directly.
            return $query->orWhereHas('group', function ($groupQuery) use ($groupSearchableFields) {
                $groupQuery->where(function ($query4) use ($groupSearchableFields) {
                    foreach ($groupSearchableFields as $field) {
                        $query4->orWhere($field, 'LIKE', '%'.$this->search.'%');
                    }
                });
            });
        });
    }

    /**
     * Checks if a column exists in the table and caches the result.
     *
     * @param  string  $table  The name of the table.
     * @param  string  $field  The column name.
     * @param  array  $columnCache  Reference to the cache array.
     * @return bool
     */
    protected function columnExists($table, $field, &$columnCache)
    {
        if (! isset($columnCache[$table])) {
            $columnCache[$table] = Schema::getColumnListing($table);
        }

        return in_array($field, $columnCache[$table]);
    }

    //    protected function initialize()
    //    {
    //        $this->heading = $this->panel()?->getHeading();
    //        $this->createChatAction = $this->panel()?->hasCreateChatAction();
    //        $this->chatsSearch = $this->panel()?->hasChatsSearch();
    //        $this->redirectToHomeAction = $this->widget
    //            ? false
    //            : $this->panel()?->hasRedirectToHomeAction();
    //    }

    protected function initialize()
    {
        // Grab the original class‐level defaults
        $defaults = get_class_vars(self::class);

        //
        // TITLE
        //
        // If current ≠ original (''), the user passed something:
        //   • null → explicit “no heading”
        //   • non‐empty string → custom heading
        //

        if ($this->heading !== $defaults['heading']) {
            // leave $this->heading as-is (null or custom string)
        } else {
            // still '', so never set → pull from panel()

            $this->heading = $this->panel()?->getHeading();
        }
        //  dd($this->heading , $defaults['heading']);

        //
        // BOOLEAN FLAGS
        //
        // Their default is null, so:
        //   • null → never set → fallback to panel()
        //   • true/false → explicit override
        // todo: update action names to match panel names
        if ($this->createChatAction === null) {
            $this->createChatAction = $this->panel()?->hasCreateChatAction();
        }

        if ($this->chatsSearch === null) {
            $this->chatsSearch = $this->panel()?->hasChatsSearch();
        }

        if ($this->redirectToHomeAction === null) {
            $this->redirectToHomeAction = $this->widget
                ? false
                : $this->panel()?->hasRedirectToHomeAction();
        }
    }

    /**
     * Resolve a dashboard route value (URL string, route name, or 'default').
     */
    protected function resolveDashboardRoute(string $route): string
    {
        // If 'default', use default Filament panel
        if ($route === 'default') {
            return $this->getDefaultFilamentPanelUrl();
        }

        // If it's a route name (contains only alphanumeric, dots, underscores, or hyphens)
        // and route exists, use route() helper
        if (preg_match('/^[a-zA-Z0-9._-]+$/', $route) && Route::has($route)) {
            return route($route);
        }

        // Otherwise treat it as a URL path
        return $route;
    }

    /**
     * Get the default Filament panel URL.
     */
    protected function getDefaultFilamentPanelUrl(): string
    {
        if (class_exists(Filament::class)) {
            $defaultPanel = Filament::getDefaultPanel();
            if ($defaultPanel) {
                return $defaultPanel->getUrl();
            }

            // Fallback to current panel if default is not available
            if (Filament::hasCurrentPanel()) {
                $currentPanel = Filament::getCurrentPanel();
                if ($currentPanel) {
                    return $currentPanel->getUrl();
                }
            }
        }

        // Ultimate fallback
        return '/';
    }
}
