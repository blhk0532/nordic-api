<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Livewire\Modals;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Reflector;
use Livewire\Component;
use ReflectionProperty;

class Modal extends Component
{
    public ?string $activeComponent;

    public array $components = [];

    public bool $sidebarOpen = false;

    public function getListeners(): array
    {
        return [
            'openWirechatModal',
            'destroyWirechatModal',
            'open-modal' => 'onOpenModal',
            'close-modal' => 'onCloseModal',
        ];
    }

    public function resetState(): void
    {
        $this->components = [];
        $this->activeComponent = null;
    }

    public function openWirechatModal($component, $arguments = [], $modalAttributes = []): void
    {
        // Handle Livewire 3 dispatch format where params might be bundled in first parameter
        if (is_array($component)) {
            if (isset($component['component'])) {
                // New format: { component: '...', arguments: {...}, modalAttributes: {...} }
                $modalAttributes = $component['modalAttributes'] ?? [];
                $arguments = $component['arguments'] ?? [];
                $component = $component['component'];
            } elseif (isset($component[0])) {
                // Array format: [component, arguments, modalAttributes]
                $componentName = $component[0];
                $arguments = $component[1] ?? [];
                $modalAttributes = $component[2] ?? [];
                $component = $componentName;
            }
        }

        $componentClass = app('livewire.finder')->resolveClassComponentClassName($component);

        // Validate that we have a valid component class
        if (! $componentClass || ! class_exists($componentClass)) {
            throw new \InvalidArgumentException("Invalid component class: {$component}");
        }

        $id = md5($component.serialize($arguments));

        $arguments = collect($arguments)
            ->merge($this->resolveComponentProps($arguments, new $componentClass))
            ->all();

        $this->components[$id] = [
            'name' => $component,
            'arguments' => $arguments,
            'modalAttributes' => array_merge(
                method_exists($componentClass, 'modalAttributes') ? $componentClass::modalAttributes() : [], // Fetch reusable modal attributes
                $modalAttributes // Allow custom overrides
            ),
        ];

        $this->activeComponent = $id;

        $this->dispatch('activeWirechatModalComponentChanged', id: $id);
    }

    public function resolveComponentProps(array $attributes, Component $component): Collection
    {
        return $this->getPublicPropertyTypes($component)
            ->intersectByKeys($attributes)
            ->map(function ($className, $propName) use ($attributes) {
                // Validate class name before resolving parameter
                if (! class_exists($className)) {
                    return null;
                }

                $resolved = $this->resolveParameter($attributes, $propName, $className);

                return $resolved;
            });
    }

    public function getPublicPropertyTypes($component): Collection
    {
        return collect($component->all())
            ->map(function ($value, $name) use ($component) {
                /* @phpstan-ignore argument.type */
                return Reflector::getParameterClassName(new ReflectionProperty($component, $name));
            })
            ->filter();
    }

    public function destroyWirechatModal($id): void
    {
        unset($this->components[$id]);
    }

    public function onOpenModal($event = null): void
    {
        if ($event && isset($event['id']) && $event['id'] === 'chats-sidebar') {
            $this->sidebarOpen = true;
        }
    }

    public function onCloseModal($event = null): void
    {
        if ($event && isset($event['id']) && $event['id'] === 'chats-sidebar') {
            $this->sidebarOpen = false;
        }
    }

    public function render()
    {
        return view('wirechat::livewire.modals.modal');
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
            throw (new ModelNotFoundException)->setModel(get_class($instance), [$parameterValue]);
        }

        return $model;
    }
}
