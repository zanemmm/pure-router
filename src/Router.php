<?php

namespace Zane\PureRouter;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zane\PureRouter\Exceptions\MiddlewareNotFoundException;
use Zane\PureRouter\Exceptions\ParameterTypeNotFoundException;
use Zane\PureRouter\Interfaces\RouteGroupInterface;
use Zane\PureRouter\Interfaces\RouteInterface;
use Zane\PureRouter\Interfaces\RouterInterface;
use Zane\PureRouter\Parameters\AbstractParameter;
use Zane\PureRouter\Parameters\AlphaNumberParameter;
use Zane\PureRouter\Parameters\AlphaParameter;
use Zane\PureRouter\Parameters\AnyParameter;
use Zane\PureRouter\Parameters\FirstLowerCharParameter;
use Zane\PureRouter\Parameters\FirstUpperCharParameter;
use Zane\PureRouter\Parameters\LowerParameter;
use Zane\PureRouter\Parameters\NumberParameter;
use Zane\PureRouter\Parameters\UpperParameter;
use Zane\PureRouter\Parameters\UpperWordParameter;

class Router implements RouterInterface
{
    /**
     * @var string
     */
    protected static $defaultParameter = 'any';

    /**
     * @var array
     */
    protected static $parameters = [
        'any' => AnyParameter::class,
        'alpha' => AlphaParameter::class,
        'alnum' => AlphaNumberParameter::class,
        'num' => NumberParameter::class,
        'flc' => FirstLowerCharParameter::class,
        'fuc' => FirstUpperCharParameter::class,
        'upper' => UpperParameter::class,
        'lower' => LowerParameter::class,
        'uword' => UpperWordParameter::class,
    ];

    /**
     * @var ResponseInterface|null
     */
    protected static $notFoundResponse = null;

    /**
     * @var array
     */
    protected static $middleware = [];

    /**
     * @var RouteGroupInterface[]
     */
    protected $routeGroups = [];

    /**
     * @var RouteGroupInterface
     */
    protected $currentGroup;

    /**
     * @var RouteInterface[]
     */
    protected $namedRoutes;

    public function __construct()
    {
        $this->currentGroup  = new RouteGroup('/');
        $this->routeGroups[] = $this->currentGroup;
    }

    public function get(string $pattern, $action): RouteInterface
    {
        return $this->match(['GET'], $pattern, $action);
    }

    public function post(string $pattern, $action): RouteInterface
    {
        return $this->match(['POST'], $pattern, $action);
    }

    public function put(string $pattern, $action): RouteInterface
    {
        return $this->match(['PUT'], $pattern, $action);
    }

    public function patch(string $pattern, $action): RouteInterface
    {
        return $this->match(['PATCH'], $pattern, $action);
    }

    public function delete(string $pattern, $action): RouteInterface
    {
        return $this->match(['DELETE'], $pattern, $action);
    }

    public function options(string $pattern, $action): RouteInterface
    {
        return $this->match(['OPTIONS'], $pattern, $action);
    }

    public function any(string $pattern, $action): RouteInterface
    {
        return $this->match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $action);
    }

    public function match(array $methods, string $pattern, $action): RouteInterface
    {
        return $this->currentGroup->addRoute($methods, $pattern, $action, $this);
    }

    /**
     * Add group to router.
     *
     * @param array $info
     * @param Closure $fn
     *
     * @return RouteGroupInterface
     */
    public function group(array $info, Closure $fn): RouteGroupInterface
    {
        $prefix     = $info['prefix'] ?? '/';
        $namespace  = $info['namespace'] ?? '\\';
        $middleware = $info['middleware'] ?? [];

        $newGroup = new RouteGroup($prefix, $namespace);
        $newGroup->middleware($middleware);

        $this->routeGroups[] = $newGroup;

        $defaultGroup = $this->currentGroup;
        $this->currentGroup = $newGroup;
        $fn->call($this, $this);
        $this->currentGroup = $defaultGroup;

        return $newGroup;
    }

    /**
     * Dispatch route for request.
     *
     * @param ServerRequestInterface $request
     *
     * @return null|ResponseInterface
     *
     * @throws MiddlewareNotFoundException
     */
    public function dispatch(ServerRequestInterface $request): ?ResponseInterface
    {
        foreach ($this->routeGroups as $routeGroup) {
            if ($routeGroup->match($request)) {
                $route = $routeGroup->findMatchRoute($request);
                if ($route instanceof RouteInterface) {
                    return $this->resolveMiddleware($route, $routeGroup)->handle($request);
                }
            }
        }

        return static::$notFoundResponse;
    }

    /**
     * Resolve middleware of route.
     *
     * @param RouteInterface $route
     * @param RouteGroupInterface $group
     *
     * @return RequestHandlerInterface
     *
     * @throws MiddlewareNotFoundException
     */
    protected function resolveMiddleware(RouteInterface $route, RouteGroupInterface $group): RequestHandlerInterface
    {
        $middleware = array_reverse(array_merge($group->middleware(), $route->middleware()));
        if (empty($middleware)) {
            return $route->action();
        }

        $middlewareInstances = [];

        foreach ($middleware as $item) {
            $middlewareInstances[] = static::getMiddleware($item);
        }

        $next = new MiddlewareHandler(array_shift($middlewareInstances), $route->action());
        foreach ($middlewareInstances as $instance) {
            $next = new MiddlewareHandler($instance, $next);
        }

        return $next;
    }

    /**
     * Get route by name.
     *
     * @param string $name
     *
     * @return null|RouteInterface
     */
    public function getNamedRoute(string $name): ?RouteInterface
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Set route name.
     *
     * @param string $name
     *
     * @param RouteInterface $route
     */
    public function addNamedRoute(string $name, RouteInterface $route): void
    {
        $this->namedRoutes[$name] = $route;
    }

    /**
     * Get parameter instance by type.
     *
     * @param string $type
     * @param string $name
     *
     * @return AbstractParameter
     *
     * @throws ParameterTypeNotFoundException
     */
    public static function getParameter(string $type, string $name): AbstractParameter
    {
        if (!isset(static::$parameters[$type])) {
            throw new ParameterTypeNotFoundException($type);
        }

        return new static::$parameters[$type]($name);
    }

    /**
     * Get default parameter.
     *
     * @param string $name
     *
     * @return AbstractParameter
     *
     * @throws ParameterTypeNotFoundException
     */
    public static function getDefaultParameter(string $name): AbstractParameter
    {
        return static::getParameter(static::$defaultParameter, $name);
    }

    /**
     * Set default parameter type.
     *
     * @param string $type
     *
     * @throws ParameterTypeNotFoundException
     */
    public static function setDefaultParameter(string $type): void
    {
        if (!isset(static::$parameters[$type])) {
            throw new ParameterTypeNotFoundException($type);
        }

        static::$defaultParameter = $type;
    }

    /**
     * Set the not found response.
     *
     * @param ResponseInterface $response
     */
    public static function setNotFoundResponse(ResponseInterface $response): void
    {
        static::$notFoundResponse = $response;
    }

    /**
     * Get middleware instance by name.
     *
     * @param string $name
     *
     * @return MiddlewareInterface
     *
     * @throws MiddlewareNotFoundException
     */
    public static function getMiddleware(string $name): MiddlewareInterface
    {
        if (!isset(static::$middleware[$name])) {
            throw new MiddlewareNotFoundException($name);
        }

        return new static::$middleware[$name]();
    }

    /**
     * Extend parameter type.
     *
     * @param string $type
     * @param string $parameterClassName
     */
    public static function extendParameter(string $type, string $parameterClassName): void
    {
        static::$parameters[$type] = $parameterClassName;
    }

    /**
     * Extend middleware.
     *
     * @param string $name
     * @param string $middlewareClassName
     */
    public static function extendMiddleware(string $name, string $middlewareClassName): void
    {
        static::$middleware[$name] = $middlewareClassName;
    }
}
