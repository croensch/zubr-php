<?php

namespace Tests\Zubr\Server;

use Zubr;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testToPsrResponse()
    {
        $request = new Zubr\Request('s', 'o', ['p' => null]);
        $serverResponse = new Zubr\Server\Response('s', 'o');
        $psrResponse = $serverResponse->toPsrResponse();
        $this->assertEquals('{"oResponse":{"oResult":null}}', (string) $psrResponse->getBody());
    }
}
