<?php
namespace Chazz\Facades;

use Chazz\Lib\Facade;

class Event extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'event';
    }
}