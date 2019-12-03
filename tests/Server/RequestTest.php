<?php

namespace Tests\Zubr\Server;

use Zubr;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testFromServerRequest()
    {
        $clientRequest = new Zubr\Client\Request('s', 'o', ['p' => null]);
        $request = Zubr\Server\Request::fromServerRequest($clientRequest->toClientRequest());
        $this->assertSame('s', $request->getServiceName());
        $this->assertSame('o', $request->getOperationName());
        $this->assertSame(['p' => null], $request->getParametersNamed());
    }
}
