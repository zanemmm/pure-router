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

    protected $method;

    protected $pattern;

    protected $action;

    protected $parameters = [];

    public function __construct(string $method, string $pattern, $action)
    {
        $this->method  = $method;
        $this->pattern = $pattern;
        $this->action  = $action;
    }

    public function match(RequestInterface $request): bool
    {
        if ($this->method !== $request->getMethod()) {
            return false;
        }

        $patternSegments = explode('/', trim($this->pattern, '/'));
        $uriSegments = explode('/', trim($request->getUri(), '/'));
        if (count($patternSegments) !== count($uriSegments)) {
            return false;
        }

        $segments = array_combine($patternSegments, $uriSegments);
        return $this->matchSegments($segments);
    }

    protected function matchSegments($segments): bool
    {
        foreach ($segments as $pattern => $uri) {
            if ($pattern[0] === self::PARAMETER_HEAD) {
                list($type, $name) = explode(self::PARAMETER_SEPARATOR, $pattern);
                // Get parameter and match this uri segment.
                $parameter = Router::getParameter($type);
                if (!$parameter->match($uri)) {
                    return false;
                }
                // Add parameter to this route.
                is_null($name) ? $this->parameters[] = $parameter : $this->parameters[$name] = $parameter;
            } elseif ($pattern !== $uri) {
                return false;
            }
        }

        return true;
    }

    public function url(array $params = []): string
    {
        // TODO: Implement url() method.
    }

    public function name(string $name = null): RouteInterface
    {
        // TODO: Implement name() method.
    }

    public function getRequest(): RequestInterface
    {
        // TODO: Implement getRequest() method.
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
