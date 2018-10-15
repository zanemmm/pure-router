<?php

namespace Zane\PureRouter;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zane\PureRouter\Exceptions\RoutePatternException;
use Zane\PureRouter\Exceptions\RouteResolveActionException;
use Zane\PureRouter\Exceptions\RouteUrlParameterNotMatchException;
use Zane\PureRouter\Interfaces\RouteInterface;
use Zane\PureRouter\Parameters\AbstractParameter;

class Route implements RouteInterface
{
    const ACTION_SEPARATOR    = '@';

    const PARAMETER_HEAD      = '$';

    const PARAMETER_SEPARATOR = '|';

    /**
     * @var null|ServerRequestInterface
     */
    protected $request = null;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $methods;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var array
     */
    protected $segments;

    /**
     * @var RequestHandlerInterface
     */
    protected $action;

    /**
     * @var string[]
     */
    protected $middleware = [];

    /**
     * @var array
     */
    protected $parameters = [];

    public function __construct(array $methods, string $pattern, $action)
    {
        $this->methods = $methods;
        $this->pattern = $pattern;
        $this->action  = $action;
    }

    /**
     * Check route and request match.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function match(ServerRequestInterface $request): bool
    {
        if (!in_array($request->getMethod(), $this->methods)) {
            return false;
        }

        $patternSegments = $this->segments ?? $this->resolvePattern();
        $uriSegments     = explode('/', trim($request->getUri(), '/'));
        if (count($patternSegments) !== count($uriSegments)) {
            return false;
        }

        $segments = array_combine($uriSegments, $patternSegments);

        return $this->matchSegments($segments);
    }

    /**
     * Explode pattern to segments and parse pattern parameters.
     *
     * @return array
     *
     * @throws
     */
    protected function resolvePattern(): array
    {
        // For root pattern.
        if ($this->pattern === '/' || empty($this->pattern)) {
            //Because the root uri segments will parse to [''].
            $this->segments = [''];

            return $this->segments;
        }
        // For others
        $this->segments = explode('/', trim($this->pattern, '/'));
        // Parse parameter of segments.
        $this->segments = array_map(function (string $segment) {
            if (empty($segment)) {
                throw new RoutePatternException($this->pattern);
            } elseif ($segment[0] === self::PARAMETER_HEAD) {
                $parameterInfo = explode(self::PARAMETER_SEPARATOR, substr($segment, 1));
                // Get name and type from parameter information.
                if (count($parameterInfo) == 2) {
                    [$name, $type] = $parameterInfo;
                } elseif (!empty($parameterInfo[0])) {
                    $name = $parameterInfo[0];
                    $type = null;
                } else {
                    throw new RoutePatternException($this->pattern);
                }
                // Get parameter and match this uri segment.
                $parameter = is_null($type) ? Router::getDefaultParameter($name) : Router::getParameter($type, $name);
                // Add parameter to this route.
                $this->parameters[$name] = $parameter;
                // Return parameter instance.
                return $parameter;
            }

            return $segment;
        }, $this->segments);

        return $this->segments;
    }

    /**
     * Check uri segments match pattern segments.
     * If pattern parameter match then set uri segment as value to parameter.
     *
     * @param $segments
     *
     * @return bool
     */
    protected function matchSegments($segments): bool
    {
        foreach ($segments as $uri => $pattern) {
            if ($pattern instanceof AbstractParameter) {
                if ($pattern->match($uri)) {
                    // set value to this parameter
                    $pattern->value($uri);
                } else {
                    return false;
                }
            } elseif ($pattern !== $uri) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get url by route pattern.
     *
     * @param array $parameters
     *
     * @return string
     */
    public function url(array $parameters = []): string
    {
        $segments = $this->segments ?? $this->resolvePattern();

        $segments = array_map(function ($segment) use ($parameters) {
            if ($segment instanceof AbstractParameter) {
                $value = $parameters[$segment->name()] ?? null;
                // Throw exception when parameter value is null or not match.
                if (is_null($value) || !$segment->match($value)) {
                    throw new RouteUrlParameterNotMatchException($segment->name());
                }

                return $value;
            }
            return $segment;
        }, $segments);

        return '/' . implode('/', $segments);
    }

    /**
     * Get or set name.
     *
     * @param string|null $name
     *
     * @return $this|string
     */
    public function name(string $name = null)
    {
        if (is_null($name)) {
            return $this->name;
        }
        $this->name = $name;

        return $this;
    }

    /**
     * Get parameter value.
     *
     * @param string|string[] $names
     *
     * @return AbstractParameter|AbstractParameter[]
     */
    public function get($names = [])
    {
        if (is_string($names)) {
            return $this->getParameter($names)->value();
        }

        if (!is_array($names)) {
            return [];
        }

        if (empty($names)) {
            $parameters = $this->parameters;
        } else {
            $parameters = $this->getParameters($names);
        }

        return array_map(function (AbstractParameter $parameter) {
            return $parameter->value();
        }, $parameters);
    }

    /**
     * Get or set methods.
     *
     * @param string[] $methods
     *
     * @return $this|string[]
     */
    public function methods(array $methods = [])
    {
        if (empty($methods)) {
            return $this->methods;
        }

        $this->methods = $methods;

        return $this;
    }


    /**
     * Get or set action.
     *
     * @param null|string|RequestHandlerInterface $action
     *
     * @return $this|RequestHandlerInterface
     *
     * @throws RouteResolveActionException
     */
    public function action($action = null)
    {
        if (is_null($action)) {
            if ($this->action instanceof RequestHandlerInterface) {
                return $this->action;
            }

            return $this->resolveAction();
        }

        $this->action = $action;

        return $this;
    }

    /**
     * Make the action to ActionHandler.
     *
     * @return ActionHandler
     *
     * @throws RouteResolveActionException
     */
    protected function resolveAction(): ActionHandler
    {
        if (is_callable($this->action)) {
            $fn = Closure::fromCallable($this->action);
            $this->action = new ActionHandler($fn, $this);

            return $this->action;
        }

        if (is_string($this->action)) {
            $actionInfo = explode(self::ACTION_SEPARATOR, $this->action);
            if (count($actionInfo) !== 2) {
                // can't parse action string
                throw new RouteResolveActionException($this->pattern);
            }

            $fn = Closure::fromCallable([new $actionInfo[0], $actionInfo[1]]);
            $this->action = new ActionHandler($fn, $this);

            return $this->action;
        }

        // Action type wrong.
        throw new RouteResolveActionException($this->pattern);
    }

    /**
     * Get or set middleware.
     *
     * @param array $names
     *
     * @return string[]|RouteInterface
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
     * Get or set HTTP request.
     *
     * @param ServerRequestInterface $request|null
     *
     * @return ServerRequestInterface|RouteInterface|null
     */
    public function request(ServerRequestInterface $request = null)
    {
        if (is_null($request)) {
            return $this->request;
        }

        $this->request = $request;

        return $this;
    }

    /**
     * Get parameter instances array by name.
     *
     * @param array $parameters
     *
     * @return AbstractParameter[]
     */
    public function getParameters(array $parameters = []): array
    {
        if (empty($parameters)) {
            return $this->parameters;
        }

        return array_intersect_key($this->parameters, array_flip($parameters));
    }

    /**
     * Get parameter instance by name.
     *
     * @param string $name
     *
     * @return AbstractParameter|null
     */
    public function getParameter(string $name): ?AbstractParameter
    {
        return $this->parameters[$name] ?? null;
    }
}
