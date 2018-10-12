<?php

namespace Zane\PureRouter\Interfaces;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zane\PureRouter\Parameters\AbstractParameter;

interface RouteInterface
{
    public function __construct(array $methods, string $pattern, RequestHandlerInterface $action);

    public function match(ServerRequestInterface $request): bool;

    public function url(array $parameter = []): string;

    public function name(string $name = null);

    public function get($names = []);

    public function action(RequestHandlerInterface $handler = null);

    public function middleware(array $names = []);

    public function request(ServerRequestInterface $request = null);

    public function getParameters(array $names = []): array;

    public function getParameter(string $name): ?AbstractParameter;
}
