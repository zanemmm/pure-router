<?php

namespace Zane\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zane\PureRouter\ActionHandler;
use Zane\PureRouter\Interfaces\RouteInterface;
use Zane\PureRouter\Route;
use Zane\PureRouter\Router;

class ActionHandlerTest extends TestCase
{
    public function testHandle()
    {
        $fn = function (ServerRequestInterface $request, RouteInterface $route) {
            return new Response(200, [], $request->getUri().$route->url());
        };
        $route = new Route(['GET'], 'world', $fn, new Router());
        $request = new ServerRequest('GET', 'hello');
        $handler = new ActionHandler($fn, $route);

        $this->assertEquals('hello/world', $handler->handle($request)->getBody()->getContents());
    }
}
