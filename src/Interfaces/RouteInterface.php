<?php

namespace Zane\PureRouter\Interfaces;

use Psr\Http\Message\ServerRequestInterface;
use Zane\PureRouter\Parameters\AbstractParameter;

interface RouteInterface
{
    public function __construct(array $methods, string $pattern, $action, RouterInterface $router);

    public function match(ServerRequestInterface $request): bool;

    public function url(array $parameter = []): string;

    public function name(string $name = null);

    public function get($names = []);

    public function methods(array $methods = []);

    public function action($action = null);

    public function middleware(array $names = []);

    public function getParameters(array $names = []): array;

    public function getParameter(string $name): ?AbstractParameter;
}
