<?php

namespace Zane\Tests;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Zane\PureRouter\Route;
use Zane\PureRouter\RouteGroup;
use Zane\PureRouter\Router;

class RouteGroupTest extends TestCase
{
    public function getRouteGroup($prefix = '/')
    {
        return new RouteGroup($prefix);
    }

    protected function getRequest($uri, $method = 'GET', array $headers = [], $body = null, $version = '1.1')
    {
        return new ServerRequest($method, $uri, $headers, $body, $version);
    }

    public function testAddRoute()
    {
        $router = new Router();
        $group = $this->getRouteGroup();
        $route1 = $group->addRoute(['GET', 'POST'], '/hello', 'world', $router);
        $route2 = new Route(['GET', 'POST'], '/hello', '\\world', $router);
        $this->assertEquals($route2, $route1);

        $group = $this->getRouteGroup('/hello');
        $route1 = $group->addRoute(['GET'], '/world', 'world', $router);
        $route2 = new Route(['GET'], 'hello/world', '\\world', $router);
        $this->assertEquals($route2, $route1);
    }

    public function testMatch()
    {
        $group = $this->getRouteGroup('/hello');
        $this->assertTrue($group->match($this->getRequest('/hello/world')));
        $this->assertTrue($group->match($this->getRequest('/hello')));
        $this->assertTrue($group->match($this->getRequest('/hello/')));
        $this->assertFalse($group->match($this->getRequest('/hell')));
        $this->assertFalse($group->match($this->getRequest('/')));

        $group = $this->getRouteGroup('/');
        $this->assertTrue($group->match($this->getRequest('/')));
        $this->assertTrue($group->match($this->getRequest('any')));
        $this->assertTrue($group->match($this->getRequest('/any')));
        $this->assertTrue($group->match($this->getRequest('/any/route')));
    }

    public function testMiddleware()
    {
        $group = $this->getRouteGroup();
        $group->middleware(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $group->middleware());
    }

    public function testFindMatchRoute()
    {
        $router = new Router();

        $group = $this->getRouteGroup();
        $helloRoute = $group->addRoute(['GET', 'POST'], '/hello', 'world', $router);
        $route = $group->findMatchRoute($this->getRequest('/hello'));
        $this->assertEquals($helloRoute, $route);

        $group = $this->getRouteGroup('/hello');
        $helloRoute = $group->addRoute(['GET'], '/', 'world', $router);
        $route = $group->findMatchRoute($this->getRequest('/hello'));
        $this->assertEquals($helloRoute, $route);

        $group = $this->getRouteGroup('/foo');
        $foobarRoute = $group->addRoute(['GET'], '/bar', 'world', $router);
        $route = $group->findMatchRoute($this->getRequest('foo/bar'));
        $this->assertEquals($foobarRoute, $route);

        $group = $this->getRouteGroup('/foo');
        $group->addRoute(['GET'], '/bar', 'world', $router);
        $route = $group->findMatchRoute($this->getRequest('foo/bar/hello'));
        $this->assertNull($route);
    }
}
