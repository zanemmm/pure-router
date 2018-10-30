<?php

namespace Zane\PureRouter\Parameters;

class UpperWordParameter extends AbstractParameter
{
    public function match(string $uriSegment): bool
    {
        if (preg_match('/^[a-zA-Z_-]+$/', $uriSegment) === 0) {
            return false;
        }

        return  $uriSegment === ucwords($uriSegment, '-_');
    }
}
