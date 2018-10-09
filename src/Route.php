<?php

namespace Zane\PureRouter;

use Psr\Http\Message\RequestInterface;
use Zane\PureRouter\Exceptions\RoutePatternException;
use Zane\PureRouter\Exceptions\RouteUrlParameterMatchException;
use Zane\PureRouter\Interfaces\RouteInterface;
use Zane\PureRouter\Parameters\AbstractParameter;

class Route implements RouteInterface
{
    const PARAMETER_HEAD      = ':';

    const PARAMETER_SEPARATOR = '|';

    protected $request = null;

    protected $name;

    protected $method;

    protected $pattern;

    protected $segments;

    protected $action;

    protected $middleware = [];

    protected $parameters = [];

    public function __construct(string $method, string $pattern, $action)
    {
        $this->method  = $method;
        $this->pattern = $pattern;
        $this->action  = $action;
    }

    /**
     * Check route and request match.
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function match(RequestInterface $request): bool
    {
        if ($this->method !== $request->getMethod()) {
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
                } elseif (count($parameterInfo) == 1) {
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
                    throw new RouteUrlParameterMatchException($segment->name());
                }

                return $value;
            }
            return $segment;
        }, $segments);

        return '/' . implode('/', $segments);
    }

    /**
     * Get or set name of route.
     *
     * @param string|null $name
     *
     * @return $this
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
     * @param string[] $names
     *
     * @return AbstractParameter|AbstractParameter[]
     */
    public function get(array $names = [])
    {
        if (count($names) === 1) {
            return $this->getParameter($names)->value();
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
     * Get or set middleware
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

        array_merge($this->middleware, $names);

        return $this;
    }


    /**
     * Get HTTP request.
     *
     * @return null|RequestInterface
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    /**
     * Set HTTP request.
     *
     * @param RequestInterface $request
     *
     * @return RouteInterface
     */
    public function setRequest(RequestInterface $request): RouteInterface
    {
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
