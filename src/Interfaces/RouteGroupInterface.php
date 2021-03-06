<?php

namespace Zane\PureRouter\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface RouteGroupInterface
{
    public function __construct(string $prefix = '/', string $namespace = '/');

    public function addRoute(array $methods, string $pattern, $action, RouterInterface $router): RouteInterface;

    public function middleware(array $names = []);

    public function match(ServerRequestInterface $request): bool;

    public function findMatchRoute(ServerRequestInterface $request): ?RouteInterface;
}
