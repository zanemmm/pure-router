<?php
namespace Zane\Tests;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Zane\PureRouter\Exceptions\RouteUrlParameterMatchException;
use Zane\PureRouter\Route;

class RouteTest extends TestCase
{
    protected function getRequest($method, $uri, array $headers = [], $body = null, $version = '1.1')
    {
        return new Request($method, $uri, $headers, $body, $version);
    }

    protected function getRoute($method, $pattern, $action)
    {
        return new Route($method, $pattern, $action);
    }

    public function testUrl()
    {
        $route = $this->getRoute('GET', '/', 'nothing');
        $url = $route->url();
        $this->assertEquals('/', $url);

        $route = $this->getRoute('GET', '', 'nothing');
        $url = $route->url();
        $this->assertEquals('/', $url);

        $route = $this->getRoute('GET', '/hello/world', 'nothing');
        $url = $route->url();
        $this->assertEquals('/hello/world', $url);

        $route = $this->getRoute('GET', '/hello/:world|any', 'nothing');
        $url = $route->url(['world' => 'better']);
        $this->assertEquals('/hello/better', $url);

        $route = $this->getRoute('GET', '/hello/:id|num', 'nothing');
        $url = $route->url(['id' => '9527']);
        $this->assertEquals('/hello/9527', $url);
        // Test parameter not match exception
        try {
            $route->url(['id' => 'notNumber']);
        } catch (RouteUrlParameterMatchException $e) {
            return;
        }
        $this->fail("Not throw RouteUrlParameterMatchException!");
    }

    public function testGetParameters()
    {
    }

    public function testMatch()
    {
        $route   = $this->getRoute('GET', '/', 'nothing');
        $request = $this->getRequest('GET', '/');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('GET', '/', 'nothing');
        $request = $this->getRequest('GET', '');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('GET', '', 'nothing');
        $request = $this->getRequest('GET', '/');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('GET', '', 'nothing');
        $request = $this->getRequest('GET', '');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('GET', '/hello', 'nothing');
        $request = $this->getRequest('GET', '/hello');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('GET', 'hello/', 'nothing');
        $request = $this->getRequest('GET', '/hello');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('GET', '/hello', 'nothing');
        $request = $this->getRequest('GET', 'hello/');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('GET', 'hello', 'nothing');
        $request = $this->getRequest('GET', 'hello');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('GET', '/hello/world', 'nothing');
        $request = $this->getRequest('GET', '/hello/world');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('GET', '/hello', 'nothing');
        $request = $this->getRequest('GET', '/world');
        $this->assertFalse($route->match($request));

        $route   = $this->getRoute('GET', '/hello', 'nothing');
        $request = $this->getRequest('GET', '/hello/world');
        $this->assertFalse($route->match($request));

        $route   = $this->getRoute('GET', '/hello/world', 'nothing');
        $request = $this->getRequest('GET', '/world');
        $this->assertFalse($route->match($request));
    }

    public function testGetParameter()
    {
    }

    public function testName()
    {
    }

    public function testGetRequest()
    {
    }
}
