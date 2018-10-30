<?php

namespace Zane\PureRouter\Parameters;

class AlphaParameter extends AbstractParameter
{
    public function match(string $uriSegment): bool
    {
        return ctype_alpha($uriSegment);
    }
}
