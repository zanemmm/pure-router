<?php

namespace Zane\PureRouter\Parameters;

use Closure;

abstract class AbstractParameter
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var mixed $value
     */
    protected $value;

    /**
     * @var bool $bound
     */
    protected $bound = false;

    /**
     * @var Closure[]
     */
    protected static $binds = [];

    public function __construct(?string $name)
    {
        $this->name = $name;
    }

    public function name(string $name = null): string
    {
        if (is_null($name)) {
            return $this->name;
        }

        $this->name = $name;

        return $this;
    }

    public function value($value = null)
    {
        if (is_null($value)) {
            if (isset(static::$binds[$this->name]) && !$this->bound) {
                $this->value = (static::$binds[$this->name])($value);
                $this->bound = true;
            }

            return $this->value;
        }

        $this->value = $value;

        return $this;
    }

    public static function bind(string $name, Closure $fn)
    {
        static::$binds[$name] = $fn;
    }

    abstract public function match(string $uriSegment): bool;
}
