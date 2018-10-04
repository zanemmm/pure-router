<?php
/**
 * Created by PhpStorm.
 * User: zane
 * Date: 18-10-4
 * Time: 下午5:22
 */

namespace Zane\PureRouter\Interfaces;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RouteMiddlewareInterface
{
    public function handle(RequestInterface $request, Closure $next): ResponseInterface;
}
