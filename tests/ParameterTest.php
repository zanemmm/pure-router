<?php

namespace Zane\Tests;

use Zane\PureRouter\Parameters\AlphaNumberParameter;
use Zane\PureRouter\Parameters\AlphaParameter;
use Zane\PureRouter\Parameters\AnyParameter;
use PHPUnit\Framework\TestCase;
use Zane\PureRouter\Parameters\FirstLowerCharParameter;
use Zane\PureRouter\Parameters\FirstUpperCharParameter;
use Zane\PureRouter\Parameters\LowerParameter;
use Zane\PureRouter\Parameters\NumberParameter;
use Zane\PureRouter\Parameters\UpperParameter;
use Zane\PureRouter\Parameters\UpperWordParameter;

class ParameterTest extends TestCase
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

    public function testMatch()
    {
        $alnum = new AlphaNumberParameter('foo');
        $alpha = new AlphaParameter('foo');
        $any = new AnyParameter('foo');
        $flc = new FirstLowerCharParameter('foo');
        $fuc = new FirstUpperCharParameter('foo');
        $lower = new LowerParameter('foo');
        $num = new NumberParameter('foo');
        $upper = new UpperParameter('foo');
        $uword = new UpperWordParameter('foo');

        $this->assertTrue($alnum->match('123'));
        $this->assertTrue($alnum->match('abc'));
        $this->assertTrue($alnum->match('ABC'));
        $this->assertTrue($alnum->match('123abcABC'));
        $this->assertFalse($alnum->match(' '));
        $this->assertFalse($alnum->match('123 abc ABC'));

        $this->assertTrue($alpha->match('abc'));
        $this->assertTrue($alpha->match('ABC'));
        $this->assertTrue($alpha->match('abcABC'));
        $this->assertFalse($alpha->match(' '));
        $this->assertFalse($alpha->match('123'));
        $this->assertFalse($alpha->match('abc ABC'));

        $this->assertTrue($any->match(''));
        $this->assertTrue($any->match('AnyThing'));

        $this->assertTrue($flc->match('tryThis'));
        $this->assertFalse($flc->match('TryThis'));
        $this->assertFalse($flc->match('123'));
        $this->assertFalse($flc->match('0tryThis'));

        $this->assertTrue($fuc->match('TryThis'));
        $this->assertFalse($fuc->match('tryThis'));
        $this->assertFalse($fuc->match('123'));
        $this->assertFalse($fuc->match('0TryThis'));

        $this->assertTrue($lower->match('abc'));
        $this->assertFalse($lower->match('abC'));
        $this->assertFalse($lower->match('ABC'));
        $this->assertFalse($lower->match('123abc'));
        $this->assertFalse($lower->match(' abc '));

        $this->assertTrue($num->match('123'));
        $this->assertTrue($num->match('000123'));
        $this->assertFalse($num->match(' 123 '));
        $this->assertFalse($num->match('123.123'));
        $this->assertFalse($num->match('abc'));

        $this->assertTrue($upper->match('ABC'));
        $this->assertFalse($upper->match(' '));
        $this->assertFalse($upper->match('aBC'));
        $this->assertFalse($upper->match('A-B-C'));
        $this->assertFalse($upper->match('36D'));
        $this->assertFalse($upper->match('123'));

        $this->assertTrue($uword->match('A-Good-Boy'));
        $this->assertTrue($uword->match('A_Good_Boy'));
        $this->assertTrue($uword->match('HELLO-Good-Boy'));
        $this->assertFalse($uword->match(''));
        $this->assertFalse($uword->match('a_Good_Boy'));
        $this->assertFalse($uword->match('123'));
    }
}
