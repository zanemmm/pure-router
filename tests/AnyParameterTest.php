<?php

namespace Zane\Tests;

use Zane\PureRouter\Parameters\AnyParameter;
use PHPUnit\Framework\TestCase;

class AnyParameterTest extends TestCase
{
    public function testValue()
    {
        $any = new AnyParameter('foo');
        $any->value('hello');
        $this->assertEquals('hello', $any->value());

        // test bind
        AnyParameter::bind('foo', function ($value) {
            return $value . ' world';
        });
        $this->assertEquals('hello world', $any->value());
        $any->value('bye');
        $this->assertEquals('bye world', $any->value());

        AnyParameter::bind('foo', function ($value) {
            return $value . ' other';
        });
        $any->value('test');
        $this->assertEquals('test other', $any->value());
    }

    public function testName()
    {
        $any = new AnyParameter('foo');
        $this->assertEquals('foo', $any->name());
        $any->name('bar');
        $this->assertEquals('bar', $any->name());
    }
}
