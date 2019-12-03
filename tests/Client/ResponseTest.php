<?php

namespace Tests\Zubr\Client;

use Zubr;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testFromClientResponse()
    {
        $request = new Zubr\Server\Request('s', 'o', ['p' => null]);
        $serverResponse = new Zubr\Server\Response($request);
        $response = Zubr\Client\Response::fromClientResponse($request, $serverResponse->toServerResponse());
        $this->assertSame(null, $response->getResult());
    }
}
