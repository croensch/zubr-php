<?php

namespace Tests\Zubr\Server;

use Zubr;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testToServerResponse()
    {
        $request = new Zubr\Server\Request('s', 'o', ['p' => null]);
        $response = new Zubr\Server\Response($request);
        $serverResponse = $response->toServerResponse();
        $this->assertEquals('{"oResponse":{"oResult":null}}', (string) $serverResponse->getBody());
    }
}
