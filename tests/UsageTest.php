<?php

namespace Zane\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zane\PureRouter\Interfaces\RouteInterface;
use Zane\PureRouter\Interfaces\RouterInterface;
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

        $router->get(':to/girl', 'Zane\\Tests\\Stubs\\TestController@index')->name('myLove');

        $router->post('world', new WorldAction());

        $response = $router->dispatch($this->getRequest('GET', '/hello'));
        $this->assertEquals('/hello', $response->getBody()->getContents());

        $response = $router->dispatch($this->getRequest('GET', '/hello/zane'));
        $this->assertEquals('zane', $response->getBody()->getContents());

        $response = $router->dispatch($this->getRequest('POST', '/world'));
        $this->assertEquals('world', $response->getBody()->getContents());

        $response = $router->dispatch($this->getRequest('GET', 'kiss/girl'));
        $this->assertEquals('kiss/girl|myLove', $response->getBody()->getContents());

        $response = $router->dispatch($this->getRequest('GET', 'undefined'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGroupUsage()
    {
        $router = new Router();

        $router->group(['prefix' => 'foo',  'namespace' => 'Zane\\Tests\\Stubs'], function (RouterInterface $router) {
            $router->get('foo', '\\TestController@index')->name('foo');
            $router->get('bar', 'TestController@index')->name('bar');
        });
        $router->group(['prefix' => 'bar'], function (RouterInterface $router) {
            $router->get('foo', function () {
                return new Response(200, [], 'second group');
            });
        });
        $router->get('root', function () {
            return new Response(200, [], 'default group');
        });

        $response = $router->dispatch($this->getRequest('GET', 'foo/foo'));
        $this->assertEquals('foo/foo|foo', $response->getBody()->getContents());

        $response = $router->dispatch($this->getRequest('GET', 'foo/bar'));
        $this->assertEquals('foo/bar|bar', $response->getBody()->getContents());

        $response = $router->dispatch($this->getRequest('GET', 'bar/foo'));
        $this->assertEquals('second group', $response->getBody()->getContents());

        $response = $router->dispatch($this->getRequest('GET', 'root'));
        $this->assertEquals('default group', $response->getBody()->getContents());
    }
}
