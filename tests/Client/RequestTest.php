<?php

namespace Tests\Zubr\Client;

use Zubr;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testToPsrRequest()
    {
        $clientRequest = new Zubr\Client\Request('s', 'o', ['p' => null]);
        $psrRequest = $clientRequest->toPsrRequest();
        $this->assertSame('POST', $psrRequest->getMethod());
        $this->assertSame('/s', (string) $psrRequest->getUri());
        // TODO: JSON
        $this->assertSame('{"oRequest":{"p":null}}', (string) $psrRequest->getBody());
    }
}
