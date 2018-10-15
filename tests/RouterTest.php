<?php

namespace Zane\Tests;

use GuzzleHttp\Psr7\ServerRequest;
use Zane\PureRouter\Parameters\AnyParameter;
use Zane\PureRouter\Parameters\NumberParameter;
use Zane\PureRouter\Router;
use PHPUnit\Framework\TestCase;
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
        $route = $router->get('/', new HelloAction())->middleware(['hello', 'world']);

        $this->assertEquals(['hello', 'world'], $route->middleware());

        $response = $router->dispatch(new ServerRequest('GET', '/'));

        $this->assertEquals("hello,hello,world", $response->getBody()->getContents());
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

    public function testGetParameter()
    {
        $this->assertInstanceOf(AnyParameter::class, Router::getParameter('any', 'test'));
    }

    /**
     * @expectedException Zane\PureRouter\Exceptions\ParameterTypeNotFoundException
     */
    public function testNotFoundParameter()
    {
        Router::getParameter('404', 'test');
    }

    public function testExtendParameter()
    {
        Router::extendParameter('test', NumberParameter::class);
        $this->assertInstanceOf(NumberParameter::class, Router::getParameter('test', 'test'));
    }
}
