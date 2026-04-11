<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Attributes\CalendarEventContent;
use ReflectionClass;
use ReflectionMethod;

trait HasEventContent
{
    public function getEventContentJs(): ?array
    {
        // Collect all templates by model
        $contentTemplates = [];

        $reflectionClass = new ReflectionClass($this);
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC + ReflectionMethod::IS_PROTECTED) as $method) {
            $attributes = $method->getAttributes(CalendarEventContent::class);

            if (! empty($attributes)) {
                $attribute = $attributes[0]->newInstance();
                $model = $attribute->model;
                $content = $this->{$method->getName()}();
                $contentTemplates[$model] = $content;
            }
        }

        // Add default if exists
        if (method_exists($this, 'defaultEventContent')) {
            $contentTemplates['_default'] = $this->defaultEventContent();
        }
        if (method_exists($this, 'eventContent')) {
            $contentTemplates['_default'] = $this->eventContent();
        }

        if (empty($contentTemplates)) {
            return null;
        }

        return $contentTemplates;
    }
}
