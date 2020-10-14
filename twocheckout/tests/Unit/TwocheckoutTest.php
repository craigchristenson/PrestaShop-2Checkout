<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TwocheckoutTest extends TestCase
{

    private $testClass;

    public function setUp()
    : void
    {
        $this->testClass = new \Twocheckout();
        parent::setUp();
    }


}
