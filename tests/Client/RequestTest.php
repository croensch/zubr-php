<?php

namespace Tests\Zubr\Client;

use Zubr;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testToClientRequest()
    {
        $request = new Zubr\Client\Request('s', 'o', ['p' => null]);
        $clientRequest = $request->toClientRequest();
        $this->assertSame('POST', $clientRequest->getMethod());
        $this->assertSame('/s', (string) $clientRequest->getUri());
        // TODO: JSON
        $this->assertSame('{"oRequest":{"p":null}}', (string) $clientRequest->getBody());
    }
}
