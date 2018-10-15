<?php
namespace Zane\Tests;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Zane\PureRouter\ActionHandler;
use Zane\PureRouter\Interfaces\RouteInterface;
use Zane\PureRouter\Parameters\AnyParameter;
use Zane\PureRouter\Parameters\NumberParameter;
use Zane\PureRouter\Route;
use Zane\PureRouter\Router;
use Zane\Tests\Stubs\HelloAction;
use Zane\Tests\Stubs\WorldAction;

class RouteTest extends TestCase
{
    protected function getRequest($uri, $method = 'GET', array $headers = [], $body = null, $version = '1.1')
    {
        return new ServerRequest($method, $uri, $headers, $body, $version);
    }

    protected function getRoute($pattern, $method = 'GET', $action = null)
    {
        if (is_null($action)) {
            $action = new HelloAction();
        }

        return new Route([$method], $pattern, $action);
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
    }

    public function testUrlWithParameter()
    {
        $route = $this->getRoute('/hello/$world|any');
        $url = $route->url(['world' => 'better']);
        $this->assertEquals('/hello/better', $url);

        $route = $this->getRoute('/$id|num');
        $url = $route->url(['id' => '9527']);
        $this->assertEquals('/9527', $url);

        $route = $this->getRoute('$id|num');
        $url = $route->url(['id' => '0']);
        $this->assertEquals('/0', $url);

        $route = $this->getRoute('/hello/$id|num');
        $url = $route->url(['id' => '9527']);
        $this->assertEquals('/hello/9527', $url);
    }

    /**
     * @expectedException Zane\PureRouter\Exceptions\RouteUrlParameterNotMatchException
     */
    public function testRouteUrlParameterNotMatch()
    {
        $route = $this->getRoute('/hello/$id|num');
        $route->url(['id' => 'notNumber']);
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
        $request = $this->getRequest('/hello', 'POST');
        $this->assertFalse($route->match($request));

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

    /**
     * @expectedException Zane\PureRouter\Exceptions\RoutePatternException
     */
    public function testMatchWrongParameterPattern()
    {
        $route   = $this->getRoute('/hello/$');
        $request = $this->getRequest('/hello/world');
        $route->match($request);
    }

    /**
     * @expectedException Zane\PureRouter\Exceptions\RoutePatternException
     */
    public function testMatchWrongUriPattern()
    {
        $route   = $this->getRoute('/hello//world');
        $request = $this->getRequest('/hello/world');
        $route->match($request);
    }

    public function testMatchWithParameter()
    {
        $route   = $this->getRoute('/$test/world');
        $request = $this->getRequest('/hello/world');
        $this->assertTrue($route->match($request));

        $route   = $this->getRoute('/$id|num/world');
        $request = $this->getRequest('/hello/world');
        $this->assertFalse($route->match($request));

        $route   = $this->getRoute('/$id|num/$name|any');
        $request = $this->getRequest('/9527/panda');
        $this->assertTrue($route->match($request));

        return $route;
    }

    /**
     * @param RouteInterface $route
     *
     * @depends testMatchWithParameter
     */
    public function testGetParameters(RouteInterface $route)
    {
        $parameters = $route->getParameters(['id', 'name']);
        $this->assertInstanceOf(NumberParameter::class, $parameters['id']);
        $this->assertInstanceOf(AnyParameter::class, $parameters['name']);

        $this->assertEquals('9527', $parameters['id']->value());
        $this->assertEquals('panda', $parameters['name']->value());


        $parameters = $route->getParameters(['id']);
        $this->assertInstanceOf(NumberParameter::class, $parameters['id']);
        $this->assertEquals('9527', $parameters['id']->value());
        $this->assertFalse(isset($parameters['name']));

        $parameters = $route->getParameters();
        $this->assertEquals(2, count($parameters));

        $parameters = $route->getParameters(['id', 'name', 'ghost']);
        $this->assertEquals(2, count($parameters));
    }

    /**
     * @param RouteInterface $route
     *
     * @depends testMatchWithParameter
     */
    public function testGetParameter(RouteInterface $route)
    {
        $parameter = $route->getParameter('id');
        $this->assertInstanceOf(NumberParameter::class, $parameter);

        $parameter = $route->getParameter('name');
        $this->assertInstanceOf(AnyParameter::class, $parameter);

        $parameter = $route->getParameter('ghost');
        $this->assertNull($parameter);
    }

    /**
     * @depends testGetParameters
     * @depends testGetParameter
     */
    public function testGet()
    {
        $route   = $this->getRoute('/$id|num/$name|any');
        $request = $this->getRequest('/9527/panda');
        $route->match($request);

        $this->assertEquals('9527', $route->get('id'));
        $this->assertEquals(['id' => '9527', 'name' => 'panda'], $route->get([]));
        $this->assertEquals(['id' => '9527', 'name' => 'panda'], $route->get(['id', 'name']));
        $this->assertEquals(['id' => '9527', 'name' => 'panda'], $route->get(['id', 'name', 'ghost']));

        $this->assertEmpty($route->get(9527));
    }

    public function testName()
    {
        $route = $this->getRoute('/');
        $route->name('hello');
        $this->assertEquals('hello', $route->name());

        $route->name('world');
        $this->assertEquals('world', $route->name());
    }

    public function testRequest()
    {
        $route = $this->getRoute('/');
        $this->assertNull($route->request());

        $request = $this->getRequest('/');
        $route->request($request);
        $this->assertEquals($request, $route->request());
    }

    public function testMethods()
    {
        $route = $this->getRoute('/');
        $this->assertEquals(['GET'], $route->methods());

        $route->methods(['POST']);
        $this->assertEquals(['POST'], $route->methods());
    }

    public function testAction()
    {
        $route = $this->getRoute('/');
        $action = new WorldAction();
        $this->assertNotEquals($action, $route->action());

        $route->action($action);
        $this->assertEquals($action, $route->action());

        // test closure action
        $closureRoute = $this->getRoute('/', 'GET', function () {
            return 'GG';
        });
        $this->assertInstanceOf(ActionHandler::class, $closureRoute->action());

        // test callable action
        $callableRoute = $this->getRoute('/', 'GET', [Router::class, 'extendParameter']);
        $this->assertInstanceOf(ActionHandler::class, $callableRoute->action());

        // test string action
        $stringRoute = $this->getRoute('/', 'GET', 'Zane\Tests\Stubs\TestController@index');
        $this->assertInstanceOf(ActionHandler::class, $stringRoute->action());
    }

    /**
     * @expectedException \Zane\PureRouter\Exceptions\RouteResolveActionException
     */
    public function testWrongStringAction()
    {
        $stringRoute = $this->getRoute('/', 'GET', 'zane|index');
        $stringRoute->action();
    }

    /**
     * @expectedException \Zane\PureRouter\Exceptions\RouteResolveActionException
     */
    public function testWrongTypeAction()
    {
        $route = $this->getRoute('/', 'GET', []);
        $route->action();
    }
}
