<?php

namespace Zane\PureRouter\Exceptions;

use Exception;
use Throwable;

class RoutePatternException extends Exception
{
    public function __construct(string $routePattern, int $code = 0, Throwable $previous = null)
    {
        $message = "Can't parse route pattern: $routePattern.";

        parent::__construct($message, $code, $previous);
    }
}
