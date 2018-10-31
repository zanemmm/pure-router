<?php

namespace Zane\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Zane\PureRouter\Interfaces\RouterInterface;
use Zane\PureRouter\Parameters\AnyParameter;
use Zane\PureRouter\Parameters\NumberParameter;
use Zane\PureRouter\RouteGroup;
use Zane\PureRouter\Router;
use Zane\Tests\Stubs\HelloAction;
use Zane\Tests\Stubs\HelloMiddleware;
use Zane\Tests\Stubs\WorldMiddleware;

class RouterTest extends TestCase
{
    public function testDispatch()
    {
        $router = new Router();
        Router::extendMiddleware('hello', HelloMiddleware::class);
        Router::extendMiddleware('world', WorldMiddleware::class);

        $router->get('/', new HelloAction())->middleware(['hello', 'world']);
        $response = $router->dispatch(new ServerRequest('GET', '/'));
        $this->assertEquals('hello,world,hello', $response->getBody()->getContents());

        $router->post('/hello', new HelloAction());
        $response = $router->dispatch(new ServerRequest('POST', '/hello'));
        $this->assertEquals('hello', $response->getBody()->getContents());

        $response = $router->dispatch(new ServerRequest('POST', '/world'));
        $this->assertNull($response);
    }

    public function testAddRoute()
    {
        $router = new Router();
        $route = $router->get('/', 'hello');
        $this->assertEquals(['GET'], $route->methods());

        $route = $router->post('/', 'hello');
        $this->assertEquals(['POST'], $route->methods());

        $route = $router->put('/', 'hello');
        $this->assertEquals(['PUT'], $route->methods());

        $route = $router->patch('/', 'hello');
        $this->assertEquals(['PATCH'], $route->methods());

        $route = $router->delete('/', 'hello');
        $this->assertEquals(['DELETE'], $route->methods());

        $route = $router->options('/', 'hello');
        $this->assertEquals(['OPTIONS'], $route->methods());

        $route = $router->any('/', 'hello');
        $this->assertEquals(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $route->methods());

        $route = $router->match(['GET', 'POST'], '/', 'hello');
        $this->assertEquals(['GET', 'POST'], $route->methods());
    }

    public function testGroup()
    {
        $router = new Router();
        $group1 = $router->group(['prefix' => '/hello'], function (RouterInterface $router) {
            $router->get('/world', 'TestController@index');
            $this->post('/girl', 'TestController@index');
        });
        $group2 = new RouteGroup('/hello');
        $group2->addRoute(['GET'], '/world', 'TestController@index', $router);
        $group2->addRoute(['POST'], '/girl', 'TestController@index', $router);

        $this->assertEquals($group1, $group2);
    }

    public function testGetNamedRoute()
    {
        $router = new Router();
        $route1 = $router->get('/hello', 'TestController@index')->name('hello');
        $route2 = $router->getNamedRoute('hello');
        $this->assertEquals($route1, $route2);
    }

    public function testGetParameter()
    {
        $this->assertInstanceOf(AnyParameter::class, Router::getParameter('any', 'test'));
    }

    /**
     * @expectedException  \Zane\PureRouter\Exceptions\ParameterTypeNotFoundException
     */
    public function testGetNotExistParameter()
    {
        $this->assertInstanceOf(AnyParameter::class, Router::getParameter('undefined', 'test'));
    }

    public function testGetDefaultParameter()
    {
        $this->assertInstanceOf(AnyParameter::class, Router::getDefaultParameter('hello'));
    }

    public function testSetDefaultParameter()
    {
        Router::setDefaultParameter('num');
        $this->assertInstanceOf(NumberParameter::class, Router::getDefaultParameter('hello'));
    }

    public function testSetNotFoundResponse()
    {
        $response1 = new Response(404, [], 'Test Set Not Found Response');
        Router::setNotFoundResponse($response1);

        $response2 = (new Router())->dispatch(new ServerRequest('GET', '/hello'));

        $this->assertEquals($response1, $response2);
    }

    /**
     * @expectedException  \Zane\PureRouter\Exceptions\ParameterTypeNotFoundException
     */
    public function testSetNotExistDefaultParameter()
    {
        Router::setDefaultParameter('undefined');
    }

    public function testGetMiddleware()
    {
        Router::extendMiddleware('hello', HelloMiddleware::class);
        $middleware = Router::getMiddleware('hello');
        $this->assertInstanceOf(HelloMiddleware::class, $middleware);
    }

    /**
     * @expectedException \Zane\PureRouter\Exceptions\MiddlewareNotFoundException
     */
    public function testGetNotExistMiddleware()
    {
        Router::getMiddleware('undefined');
    }

    public function testExtendParameter()
    {
        Router::extendParameter('test', NumberParameter::class);
        $this->assertInstanceOf(NumberParameter::class, Router::getParameter('test', 'test'));
    }
}
