<?php

namespace Zane\PureRouter;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Zane\PureRouter\Interfaces\RouteGroupInterface;
use Zane\PureRouter\Interfaces\RouteInterface;
use Zane\PureRouter\Interfaces\RouterInterface;
use Zane\PureRouter\Parameters\AbstractParameter;

class Router implements RouterInterface
{
    public function get(string $pattern, $action): RouteInterface
    {
        // TODO: Implement get() method.
    }

    public function post(string $pattern, $action): RouteInterface
    {
        // TODO: Implement post() method.
    }

    public function put(string $pattern, $action): RouteInterface
    {
        // TODO: Implement put() method.
    }

    public function patch(string $pattern, $action): RouteInterface
    {
        // TODO: Implement patch() method.
    }

    public function delete(string $pattern, $action): RouteInterface
    {
        // TODO: Implement delete() method.
    }

    public function options(string $pattern, $action): RouteInterface
    {
        // TODO: Implement options() method.
    }

    public function any(string $pattern, $action): RouteInterface
    {
        // TODO: Implement any() method.
    }

    public function match(array $methods, string $pattern, $action): RouteInterface
    {
        // TODO: Implement match() method.
    }

    public function group(string $prefix, Closure $fn): RouteGroupInterface
    {
        // TODO: Implement group() method.
    }

    public function dispatch(RequestInterface $request): ResponseInterface
    {
        // TODO: Implement dispatch() method.
    }

    public function getRoute(string $name): RouteInterface
    {
        // TODO: Implement getRoute() method.
    }

    public static function getParameter(string $type): AbstractParameter
    {
        // TODO: Implement getParameter() method.
    }

    public static function getMiddleware(string $name): MiddlewareInterface
    {
        // TODO: Implement getMiddleware() method.
    }

    public static function extendParameter(string $type, string $parameterClassName): void
    {
        // TODO: Implement extendParameter() method.
    }

    public static function extendMiddleware(string $name, string $middlewareClassName): void
    {
        // TODO: Implement extendMiddleware() method.
    }
}
