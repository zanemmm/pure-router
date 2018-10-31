<?php

namespace Zane\PureRouter\Exceptions;

use Exception;
use Throwable;

class ParameterTypeNotFoundException extends Exception
{
    public function __construct(string $parameterType = '', int $code = 0, Throwable $previous = null)
    {
        $message = "Parameter type <$parameterType> not found.";

        parent::__construct($message, $code, $previous);
    }
}
