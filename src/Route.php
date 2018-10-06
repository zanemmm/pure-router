<?php

namespace Zane\PureRouter;

use Psr\Http\Message\RequestInterface;
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

    protected $parameters = [];

    public function __construct(string $method, string $pattern, $action)
    {
        $this->method  = $method;
        $this->pattern = $pattern;
        $this->action  = $action;
    }

    /**
     * Check route and request match.
     * If match then save the request.
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

        // If match then save the request.
        if ($this->matchSegments($segments)) {
            $this->request = $request;
            return true;
        }

        return false;
    }

    /**
     * Explode pattern to segments and parse pattern parameters.
     *
     * @return array
     */
    protected function resolvePattern(): array
    {
        $this->segments = explode('/', trim($this->pattern, '/'));

        foreach ($this->segments as $key => $segment) {
            if (strlen($segment) > 2 && $segment[0] === self::PARAMETER_HEAD) {
                [$type, $name] = explode(self::PARAMETER_SEPARATOR, substr($segment, 1));
                if (is_null($type) || is_null($name)) {
                    // TODO: throw a exception
                }
                // Get parameter and match this uri segment.
                $parameter = Router::getParameter($type, $name);
                // Add parameter to this route.
                $this->parameters[$name] = $parameter;
                // Replace segment with parameter instance.
                $this->segments[$key] = $parameter;
            }
        }

        return $this->segments;
    }

    /**
     * Check uri segments match pattern segments
     * If parameter match then set uri segment as value to parameter.
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

    public function url(array $parameters = []): string
    {
        $segments = $this->segments ?? $this->resolvePattern();
        foreach ($segments as $key => $segment) {
            if ($segment instanceof AbstractParameter) {
                // TODO: throw a exception when parameter not match
                $segments[$key] = $parameters[$segment->name()];
            }
        }

        return '/' . implode('/', $segments);
    }

    /**
     * Get or set name of route
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
     * Get the match request or null if not match
     *
     * @return null|RequestInterface
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    public function getParameters(array $params = []): array
    {
        // TODO: Implement getParams() method.
    }

    public function getParameter(string $name): AbstractParameter
    {
        // TODO: Implement getParam() method.
    }
}
