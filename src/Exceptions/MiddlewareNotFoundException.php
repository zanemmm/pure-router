<?php

namespace Zane\PureRouter\Exceptions;

use Exception;
use Throwable;

class MiddlewareNotFoundException extends Exception
{
    public function __construct(string $middlewareName = '', int $code = 0, Throwable $previous = null)
    {
        $message = "Middleware <$middlewareName> not found.";

        parent::__construct($message, $code, $previous);
    }
}
