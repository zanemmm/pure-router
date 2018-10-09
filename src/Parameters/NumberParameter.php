<?php

namespace Zane\PureRouter\Parameters;

class NumberParameter extends AbstractParameter
{
    public function match(string $uriSegment): bool
    {
        return ctype_digit($uriSegment);
    }
}
