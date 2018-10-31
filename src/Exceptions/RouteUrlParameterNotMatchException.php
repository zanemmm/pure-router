<?php

namespace Zane\PureRouter\Exceptions;

use Exception;
use Throwable;

class RouteUrlParameterNotMatchException extends Exception
{
    public function __construct(string $parameterName = '', int $code = 0, Throwable $previous = null)
    {
        $message = "Get url failed, route parameter <$parameterName> not match.";
        parent::__construct($message, $code, $previous);
    }
}
