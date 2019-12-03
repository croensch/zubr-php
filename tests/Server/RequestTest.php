<?php

namespace Tests\Zubr\Server;

use Zubr;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testFromServerRequest()
    {
        $clientRequest = new Zubr\Client\Request('s', 'o', ['p' => null]);
        $serverRequest = Zubr\Server\Request::fromPsrRequest($clientRequest->toPsrRequest());
        $this->assertSame('s', $serverRequest->getServiceName());
        $this->assertSame('o', $serverRequest->getOperationName());
        $this->assertSame(['p' => null], $serverRequest->getParametersNamed());
    }
}
