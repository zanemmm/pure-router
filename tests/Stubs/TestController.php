<?php

namespace Zane\Tests\Stubs;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Zane\PureRouter\Interfaces\RouteInterface;

class TestController
{
    public function index(RequestInterface $request, RouteInterface $route)
    {
        return new Response(200, [], $request->getUri() . '|' . $route->name());
    }
}
