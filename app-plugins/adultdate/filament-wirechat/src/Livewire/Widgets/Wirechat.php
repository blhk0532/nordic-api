<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Livewire\Widgets;

use AdultDate\FilamentWirechat\Models\Conversation;
use Adultdate\Wirechat\Livewire\Concerns\HasPanel;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Reflector;
use Livewire\Component;
use Livewire\Mechanisms\ComponentRegistry;
use ReflectionProperty;

class Wirechat extends Component
{
    use HasPanel;

    public ?string $activeWirechatWidgetComponent = null;

    public $selectedConversationId = null;

    public array $widgetComponents = [];

    public static function modalAttributes(): array
    {
        return [
            'closeOnEscape' => true,
            'closeOnEscapeIsForceful' => false,
            'dispatchCloseEvent' => true,
            'destroyOnClose' => true,
        ];
    }

    public function resetState(): void
    {
        $this->widgetComponents = [];
        $this->activeWirechatWidgetComponent = null;
        $this->selectedConversationId = null;
    }

    public function openChatWidget($conversation, $arguments = [], $modalAttributes = []): void
    {
        $component = 'wirechat.chat';
        // $componentClass = app(ComponentRegistry::class)->getClass($component);

        // Generate a unique ID using the conversationId and arguments
        $id = md5($component.$conversation.serialize($arguments));

        // Merge modal attributes with defaults
        $defaultModalAttributes = [
            'closeOnEscape' => true,
            'closeOnEscapeIsForceful' => true,
            'dispatchCloseEvent' => true,
            'destroyOnClose' => true,
        ];
        $modalAttributes = array_merge($defaultModalAttributes, $modalAttributes);
        $this->widgetComponents = [
            $id => [
                'name' => $component,
                // 'conversationId' => $conversation,
                'conversation' => $conversation,
                'modalAttributes' => $modalAttributes,
            ],
        ];

        $this->activeWirechatWidgetComponent = $id;

        // Set the selected conversationId
        $this->selectedConversationId = $conversation;

        /* ! Changed listener name to activeChatWidgetComponentChanged to not interfere with main modal */
        $this->dispatch('activeChatWidgetComponentChanged', id: $id);
    }

    public function resolveComponentProps(array $attributes, Component $component): Collection
    {

        return $this->getPublicPropertyTypes($component)
            ->intersectByKeys($attributes)
            ->map(function ($className, $propName) use ($attributes) {
                $resolved = $this->resolveParameter($attributes, $propName, $className);

                return $resolved;
            });
    }

    /**
     * @param  object  $component
     * @return Collection<string|null>
     */
    public function getPublicPropertyTypes($component): Collection
    {
        $types = collect($component->all())
            ->map(function ($value, $name) use ($component) {
                /* @phpstan-ignore argument.type */
                return Reflector::getParameterClassName(new ReflectionProperty($component, $name));
            })
            ->filter();

        return $types;

    }

    public function destroyChatWidget($id): void
    {
        unset($this->widgetComponents[$id]);
    }

    public function getListeners(): array
    {
        return [
            'openChatWidget',
            'destroyChatWidget',
            'closeChatWidget',
            'open-chat' => 'openChatWidget',

        ];
    }

    public function mount(): void
    {

        abort_unless(auth()->check(), 401);

    }

    public function render()
    {
        return view('wirechat::livewire.widgets.wire-chat');
    }

    protected function resolveParameter($attributes, $parameterName, $parameterClassName)
    {
        $parameterValue = $attributes[$parameterName];

        if ($parameterValue instanceof UrlRoutable) {
            return $parameterValue;
        }

        if (enum_exists($parameterClassName)) {
            /* @phpstan-ignore staticMethod.notFound */
            $enum = $parameterClassName::tryFrom($parameterValue);

            if ($enum !== null) {
                return $enum;
            }
        }

        $instance = app()->make($parameterClassName);

        if (! $model = $instance->resolveRouteBinding($parameterValue)) {
            throw (new ModelNotFoundException)->setModel(Conversation::class, [$parameterValue]);
        }

        return $model;
    }
}
