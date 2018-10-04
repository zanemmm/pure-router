<?php
/**
 * Created by PhpStorm.
 * User: zane
 * Date: 18-10-4
 * Time: 下午5:16
 */

namespace Zane\PureRouter\Interfaces;

use Psr\Http\Message\RequestInterface;

interface RouteInterface
{
    public function __construct(string $method, string $pattern, $action);

    public function match(RequestInterface $request): bool;

    public function url(array $params = []): string;

    public function name(string $name = null): self;

    public function getParams(array $params): array;

    public function getParam(string $name);
}
