<?php

namespace Zane\PureRouter\Exceptions;

use Exception;
use Throwable;

class RouteResolveActionException extends Exception
{
    public function __construct(string $routePattern = '', int $code = 0, Throwable $previous = null)
    {
        $message = "Can't resolve the action for the route which pattern is <$routePattern>";
        parent::__construct($message, $code, $previous);
    }
}
