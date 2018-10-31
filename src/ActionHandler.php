<?php

namespace Zane\PureRouter;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zane\PureRouter\Interfaces\RouteInterface;

class ActionHandler implements RequestHandlerInterface
{
    protected $fn;

    protected $route;

    public function __construct(Closure $fn, RouteInterface $route)
    {
        $this->fn = $fn;
        $this->route = $route;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->fn)($request, $this->route);
    }
}
