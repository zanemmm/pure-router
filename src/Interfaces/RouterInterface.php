<?php

namespace Zane\PureRouter\Interfaces;

use Closure;
use Psr\Http\Server\MiddlewareInterface;
use Zane\PureRouter\Parameters\AbstractParameter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RouterInterface
{
    public function get(string $pattern, $action): RouteInterface;

    public function post(string $pattern, $action): RouteInterface;

    public function put(string $pattern, $action): RouteInterface;

    public function patch(string $pattern, $action): RouteInterface;

    public function delete(string $pattern, $action): RouteInterface;

    public function options(string $pattern, $action): RouteInterface;

    public function any(string $pattern, $action): RouteInterface;

    public function match(array $methods, string $pattern, $action): RouteInterface;

    public function group(string $prefix, Closure $fn): RouteGroupInterface;

    public function dispatch(RequestInterface $request): ResponseInterface;

    public function getRoute(string $name): RouteInterface;

    public static function getParameter(string $type, string $name): AbstractParameter;

    public static function getMiddleware(string $name): MiddlewareInterface;

    public static function extendParameter(string $type, string $parameterClassName): void;

    public static function extendMiddleware(string $name, string $middlewareClassName): void;
}
