<?php

namespace Zane\PureRouter\Interfaces;

use Psr\Http\Message\RequestInterface;
use Zane\PureRouter\Parameters\AbstractParameter;

interface RouteInterface
{
    public function __construct(string $method, string $pattern, $action);

    public function match(RequestInterface $request): bool;

    public function url(array $params = []): string;

    public function name(string $name = null): self;

    public function getRequest(): ?RequestInterface;

    public function getParameters(array $params = []): array;

    public function getParameter(string $name): AbstractParameter;
}
