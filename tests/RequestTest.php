<?php

namespace Tests\Zubr;

use Zubr;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorGetter()
    {
        $request = new Zubr\Request('s', 'o', ['p' => null]);
        $this->assertSame('s', $request->getServiceName());
        $this->assertSame('o', $request->getOperationName());
        $this->assertSame(['p' => null], $request->getParametersNamed());
    }
}
