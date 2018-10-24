<?php

namespace Zane\PureRouter;

use Psr\Http\Message\ServerRequestInterface;
use Zane\PureRouter\Interfaces\RouteGroupInterface;
use Zane\PureRouter\Interfaces\RouteInterface;
use Zane\PureRouter\Interfaces\RouterInterface;

class RouteGroup implements RouteGroupInterface
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var RouteInterface[][]
     */
    protected $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'PATCH'  => [],
        'DELETE' => []
    ];

    /**
     * @var string[]
     */
    protected $middleware = [];

    public function __construct(string $prefix = '/', string $namespace = '\\')
    {
        $this->prefix    = $prefix;
        $this->namespace = $namespace;
    }

    /**
     * Create route instance and add to group.
     *
     * @param array $methods
     * @param string $pattern
     * @param $action
     * @param RouterInterface $router
     *
     * @return RouteInterface
     */
    public function addRoute(array $methods, string $pattern, $action, RouterInterface $router): RouteInterface
    {
        $pattern = trim($this->prefix, '/') . '/' . trim($pattern, '/');

        if (is_string($action)) {
            $action = rtrim($this->namespace, '\\') . '\\' . ltrim($action, '\\');
        }

        $route = new Route($methods, $pattern, $action, $router);
        foreach ($methods as $method) {
            $this->routes[$method][] = $route;
        }

        return $route;
    }

    /**
     * Get or append middleware.
     *
     * @param array $names
     *
     * @return string[]|RouteGroupInterface
     */
    public function middleware(array $names = [])
    {
        if (empty($names)) {
            return $this->middleware;
        }

        $this->middleware = array_merge($this->middleware, $names);

        return $this;
    }

    /**
     * Check whether the request matches the group by prefix.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function match(ServerRequestInterface $request): bool
    {
        if ($this->prefix === '/') {
            return true;
        }

        $prefix = '/' . trim($this->prefix, '/') . '/';
        $uri    = '/' . trim($request->getUri(), '/') . '/';

        if (strpos($uri, $prefix) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Find the route that matches request.
     *
     * @param ServerRequestInterface $request
     *
     * @return RouteInterface|null
     */
    public function findMatchRoute(ServerRequestInterface $request): ?RouteInterface
    {
        // Filter routes by method.
        $routes = $this->routes[$request->getMethod()] ?? [];

        foreach ($routes as $route) {
            if ($route->match($request)) {
                return $route;
            }
        }

        return null;
    }
}
