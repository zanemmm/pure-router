<?php

namespace Zane\PureRouter\Parameters;

class AnyParameter extends AbstractParameter
{
    public function match(string $uriSegment): bool
    {
        return true;
    }
}
