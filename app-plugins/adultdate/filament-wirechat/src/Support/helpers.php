<?php

declare(strict_types=1);
use Adultdate\Wirechat\Facades\Wirechat;
use Adultdate\Wirechat\Facades\WirechatColor;

if (! function_exists('wirechat')) {
    /**
     * Get the Wirechat service instance.
     */
    function wirechat()
    {
        return Wirechat::getFacadeRoot();
    }
}

if (! function_exists('wirechatColor')) {
    /**
     * Get the Wirechat Color service instance.
     */
    function wirechatColor()
    {
        return WirechatColor::getFacadeRoot();
    }
}
