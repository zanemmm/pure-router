<?php

namespace Zane\PureRouter\Parameters;

class LowerParameter extends AbstractParameter
{
    public function match(string $uriSegment): bool
    {
        return ctype_lower($uriSegment);
    }
}
