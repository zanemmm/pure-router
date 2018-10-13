<?php

namespace Zane\Tests\Stubs;

use GuzzleHttp\Psr7\Response;

class TestController
{
    public function index()
    {
        return new Response();
    }
}
