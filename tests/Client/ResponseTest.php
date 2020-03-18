<?php

namespace Tests\Zubr\Client;

use Zubr;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testFromClientResponse()
    {
        $request = new Zubr\Request('s', 'o', ['p' => null]);
        $serverResponse = new Zubr\Server\Response('s', 'o');
        $clientResponse = Zubr\Client\Response::fromPsrResponse($request, $serverResponse->toPsrResponse());
        $this->assertSame(null, $clientResponse->getResult());
    }
}
