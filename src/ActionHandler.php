<?php

namespace Zane\PureRouter;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zane\PureRouter\Interfaces\RouteInterface;

class ActionHandler implements RequestHandlerInterface
{
    protected $action;

    protected $route;

    public function __construct(Closure $action, RouteInterface $route)
    {
        $this->action = $action;
        $this->route  = $route;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->action)($request, $this->route);
    }
}
