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

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get or set parameter name.
     *
     * @param string|null $name
     *
     * @return string
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
     * Get the parameter value.
     *
     * @param mixed $value
     *
     * @return $this|mixed
     */
    public function value($value = null)
    {
        if (is_null($value)) {
            if (isset(static::$binds[$this->name]) && !$this->bound) {
                $this->value = (static::$binds[$this->name])($this->value);
                $this->bound = true;
            }

            return $this->value;
        }

        $this->value = $value;
        $this->bound = false;

        return $this;
    }

    /**
     * Set bind closure for specified parameter.
     *
     * @param string $name
     * @param Closure $fn
     */
    public static function bind(string $name, Closure $fn): void
    {
        static::$binds[$name] = $fn;
    }

    abstract public function match(string $uriSegment): bool;
}
