<?php

namespace Tests\Zubr;

use Zubr;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Zubr\Request
     */
    private $request;

    /**
     * @var Zubr\Response
     */
    private $response;

    public function setUp() : void
    {
        parent::setUp();
        $this->request = new Zubr\Request('s', 'o', ['p' => null]);
        $this->response = new Zubr\Response('s', 'o');
    }

    public function tearDown() : void
    {
        $this->request = null;
        $this->response = null;
        parent::tearDown();
    }

    /*public function testGetter()
    {
        $this->response = new Zubr\Response($this->request);
        $this->assertEquals($this->request, $this->response->getRequest());
    }*/

    public function testSetFaultOrResult()
    {
        $fault = new Zubr\Fault('test');
        $this->assertNull($this->response->getFault(), 'fault not set');
        $this->assertNull($this->response->getResult(), 'result not set');
        $this->response->setFault($fault);
        $this->assertSame($fault, $this->response->getFault(), 'fault set');
        $this->assertNull($this->response->getResult(), 'result not set');
        $this->response->setResult(['r' => null]);
        $this->assertNull($this->response->getFault(), 'fault unset');
        $this->assertSame(['r' => null], $this->response->getResult(), 'result set');
        $this->response->setFault($fault);
        $this->assertSame($fault, $this->response->getFault(), 'fault set');
        $this->assertNull($this->response->getResult(), 'result unset');
    }
}
