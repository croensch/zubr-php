<?php

namespace Zubr;

class HttpServerResponse extends ServerResponse
{
    const STATUS_CODE_OK = 200;
    const STATUS_CODE_BAD_REQUEST = 400;
    const STATUS_CODE_I_S_E = 500;
    const REASON_PHRASE_OK = 'OK';
    const REASON_PHRASE_BAD_REQUEST = 'Bad Request';
    const REASON_PHRASE_I_S_E = 'Internal Server Error';

    /**
     * @var int
     */
    protected $httpStatusCode;

    /**
     * @var string
     */
    protected $httpReasonPhrase;

    public function __construct(Request $request, int $httpStatusCode = null, string $httpReasonPhrase = null)
    {
        parent::__construct($request);
        $this->httpStatusCode = $httpStatusCode;
        $this->httpReasonPhrase = $httpReasonPhrase;
    }

    /**
     * @return array
     */
    protected function getData() : array
    {
        $data = parent::getData();
        $operationName = $this->getRequest()->getOperationName();
        $operationResponseKey = "${operationName}Response";
        $data[$operationResponseKey]['statusCode'] = $this->httpStatusCode;
        $data[$operationResponseKey]['reasonPhrase'] = $this->httpReasonPhrase;
        return $data;
    }
}
