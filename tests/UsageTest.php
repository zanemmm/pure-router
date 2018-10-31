<?php

namespace Zane\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zane\PureRouter\Interfaces\RouteInterface;
use Zane\PureRouter\Router;
use Zane\Tests\Stubs\WorldAction;

class UsageTest extends TestCase
{
    public function getRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1',
        array $serverParams = []
    ) {
        return new ServerRequest($method, $uri, $headers, $body, $version, $serverParams);
    }

    public function testSimpleUsage()
    {
        Router::setDefaultParameter('any');
        Router::setNotFoundResponse(new Response(404, [], '404 NOT FOUND!'));
        $router = new Router();

        $router->get('hello', function (ServerRequestInterface $request) {
            return new Response(200, [], $request->getUri());
        });

        $router->get('hello/:name', function (ServerRequestInterface $request, RouteInterface $route) {
            return new Response(200, [], $route->get('name'));
        });

        $router->post('world', new WorldAction());

        $response = $router->dispatch($this->getRequest('GET', '/hello'));
        $this->assertEquals('/hello', $response->getBody()->getContents());

        $response = $router->dispatch($this->getRequest('GET', '/hello/zane'));
        $this->assertEquals('zane', $response->getBody()->getContents());

        $response = $router->dispatch($this->getRequest('POST', '/world'));
        $this->assertEquals('world', $response->getBody()->getContents());
    }
}
