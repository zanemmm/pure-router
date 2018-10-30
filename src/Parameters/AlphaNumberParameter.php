<?php

namespace Zane\PureRouter\Parameters;

class AlphaNumberParameter extends AbstractParameter
{
    public function match(string $uriSegment): bool
    {
        return ctype_alnum($uriSegment);
    }
}
