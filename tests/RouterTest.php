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
