<?php

namespace Zane\PureRouter\Parameters;

class FirstUpperCharParameter extends AbstractParameter
{
    public function match(string $uriSegment): bool
    {
        return ctype_alpha($uriSegment) && ($uriSegment === ucfirst($uriSegment));
    }
}
