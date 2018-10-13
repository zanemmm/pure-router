<?php

namespace Zane\PureRouter;

use Psr\Http\Message\ServerRequestInterface;
use Zane\PureRouter\Interfaces\RouteGroupInterface;
use Zane\PureRouter\Interfaces\RouteInterface;

class RouteGroup implements RouteGroupInterface
{
    /**
     * @var string
     */
    protected $prefix;

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

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function addRoute(array $methods, string $pattern, $action): RouteInterface
    {
        $pattern = trim($this->prefix, '/') . '/' . trim($pattern, '/');

        $route = new Route($methods, $pattern, $action);
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
        if (empty($middleware)) {
            return $this->middleware;
        }

        $this->middleware = array_merge($this->middleware, $middleware);

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
                // Set request to route.
                $route->request($request);
                // Set group middleware to route
                $route->middleware($this->middleware);

                return $route;
            }
        }

        return null;
    }
}
