<?php
/**
 * Created by PhpStorm.
 * User: zane
 * Date: 18-10-4
 * Time: 下午4:53
 */

namespace Zane\PureRouter\Interfaces;

use Closure;
use Zane\PureRouter\Params\AbstractParam;
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

    public static function extendParams(string $name, AbstractParam $param);

    public static function extendMiddleware(string $name, RouteMiddlewareInterface $middleware);
}
