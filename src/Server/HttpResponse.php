<?php

namespace Zubr\Server;

class HttpResponse extends Response
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
     * @var string|null
     */
    protected $httpReasonPhrase;

    public function __construct(/*Request $request, */int $statusCode, string $reasonPhrase = null)
    {
        #parent::__construct($request);
        $this->httpStatusCode = $statusCode;
        $this->httpReasonPhrase = $reasonPhrase;
    }

    public function getStatusCode() : int
    {
        return $this->httpStatusCode;
    }

    public function getReasonPhrase() : ?string
    {
        return $this->httpReasonPhrase;
    }

    /**
     * @return array
     */
    protected function getData() : array
    {
        $data = parent::getData();
        $fault = $this->getFault();
        if ($fault === null) {
            $operationName = $this->getRequest()->getOperationName();
            $operationResponseKey = "${operationName}Response";
            $data[$operationResponseKey]['statusCode'] = $this->httpStatusCode;
            if ($this->httpReasonPhrase !== null) {
                $data[$operationResponseKey]['reasonPhrase'] = $this->httpReasonPhrase;
            }
        } else {
            $data['Fault']['statusCode'] = $this->httpStatusCode;
            $data['Fault']['reasonPhrase'] = $this->httpReasonPhrase;
        }
        return $data;
    }
}
