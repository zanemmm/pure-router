<?php

namespace Zane\PureRouter\Interfaces;

use Psr\Http\Message\RequestInterface;

interface RouteGroupInterface
{
    public function addRoute(RouteInterface $route): RouteInterface;

    public function middleware(string ...$middleware): self;

    public function match(RequestInterface $request): bool;
}
