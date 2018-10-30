<?php

namespace Zane\PureRouter\Parameters;

class UpperParameter extends AbstractParameter
{
    public function match(string $uriSegment): bool
    {
        return ctype_upper($uriSegment);
    }
}
