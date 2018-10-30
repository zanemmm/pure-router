<?php

namespace Zane\PureRouter\Parameters;

class FirstLowerCharParameter extends AbstractParameter
{
    public function match(string $uriSegment): bool
    {
        return ctype_alpha($uriSegment) && ($uriSegment === lcfirst($uriSegment));
    }
}
