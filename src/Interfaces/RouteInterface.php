<?php

namespace Zane\PureRouter\Interfaces;

use Psr\Http\Message\RequestInterface;
use Zane\PureRouter\Parameters\AbstractParameter;

interface RouteInterface
{
    public function __construct(string $method, string $pattern, $action);

    public function match(RequestInterface $request): bool;

    public function url(array $parameter = []): string;

    public function name(string $name = null);

    public function get(array $names = []);

    public function middleware(array $names = []);

    public function getRequest(): ?RequestInterface;

    public function setRequest(RequestInterface $request): self;

    public function getParameters(array $names = []): array;

    public function getParameter(string $name): ?AbstractParameter;
}
