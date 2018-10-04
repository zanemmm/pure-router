<?php
/**
 * Created by PhpStorm.
 * User: zane
 * Date: 18-10-4
 * Time: 下午5:14
 */

namespace Zane\PureRouter\Interfaces;

use Psr\Http\Message\RequestInterface;

interface RouteGroupInterface
{
    public function addRoute(RouteInterface $route): RouteInterface;

    public function middleware(...$middleware): self;

    public function match(RequestInterface $request): bool;
}
