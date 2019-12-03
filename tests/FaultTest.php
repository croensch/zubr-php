<?php

namespace Tests\Zubr;

use Zubr;

class FaultTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorGetterSetter()
    {
        $fault = new Zubr\Fault('test');
        $this->assertSame('test', $fault->getMessage());
        $this->assertNull($fault->getDetail());
        $fault->setDetail(new \stdClass);
        $this->assertEquals(new \stdClass, $fault->getDetail());
    }
}
