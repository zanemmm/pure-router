<?php
namespace Zane\Tests;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Zane\PureRouter\Route;

class RouteTest extends TestCase
{
    protected function getRequest($uri, $method = 'GET', array $headers = [], $body = null, $version = '1.1')
    {
        return new Request($method, $uri, $headers, $body, $version);
    }

    protected function getRoute($pattern, $method = 'GET', $action = null)
    {
        if (is_null($action)) {
            $action = function () {
                return 'hello world!';
            };
        }

        return new Route($method, $pattern, $action);
    }

    public function testUrl()
    {
        $route = $this->getRoute('/');
        $url = $route->url();
        $this->assertEquals('/', $url);

        $route = $this->getRoute('');
        $url = $route->url();
        $this->assertEquals('/', $url);

        $route = $this->getRoute('/hello/world');
        $url = $route->url();
        $this->assertEquals('/hello/world', $url);

        $route = $this->getRoute('/hello/:world|any');
        $url = $route->url(['world' => 'better']);
        $this->assertEquals('/hello/better', $url);

        $route = $this->getRoute('/hello/:id|num');
        $url = $route->url(['id' => '9527']);
        $this->assertEquals('/hello/9527', $url);
    }

    /**
     * @expectedException Zane\PureRouter\Exceptions\RouteUrlParameterNotMatchException
     */
    public function testRouteUrlParameterNotMatch()
    {
        $route = $this->getRoute('/hello/:id|num');
        $route->url(['id' => 'notNumber']);
    }

    public function testGetParameters()
    {
    }

    public function testMatch()
    {
        $route   = $this->getRoute('/');
        $request = $this->getRequest('/');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('/');
        $request = $this->getRequest('');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('');
        $request = $this->getRequest('/');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('');
        $request = $this->getRequest('');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('/hello');
        $request = $this->getRequest('/hello');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('hello/');
        $request = $this->getRequest('/hello');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('/hello');
        $request = $this->getRequest('hello/');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('hello');
        $request = $this->getRequest('hello');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('/hello/world');
        $request = $this->getRequest('/hello/world');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('/hello');
        $request = $this->getRequest('/world');
        $this->assertFalse($route->match($request));

        $route   = $this->getRoute('/hello');
        $request = $this->getRequest('/hello/world');
        $this->assertFalse($route->match($request));

        $route   = $this->getRoute('/hello/world');
        $request = $this->getRequest('/world');
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
