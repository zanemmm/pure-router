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
use Zane\PureRouter\Parameters\AnyParameter;

class Router implements RouterInterface
{
    protected static $parameters = [
        'any' => AnyParameter::class
    ];

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

    public static function getParameter(string $type, string $name): AbstractParameter
    {
        if (!isset(static::$parameters[$type])) {
            // TODO: throw a exception
        }

        return new static::$parameters[$type]($name);
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
