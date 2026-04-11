<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Model;

#[Attribute(Attribute::TARGET_METHOD)]
class CalendarResourceLabelContent
{
    /**
     * @param  class-string<Model>  $model
     */
    public function __construct(public string $model) {}
}
