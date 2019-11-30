<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

require_once(__DIR__ . '/vendor/autoload.php');

function myOperation ($in) {
    return $in * 42;
}

$requestBody = json_encode(['myOperationRequest' => ['in' => 1337]], JSON_PRETTY_PRINT);

$request = json_decode($requestBody, true);

$operationRequestWrapper = key($request);

$operation = preg_filter('/^(.*?)Request$/', '$1', $operationRequestWrapper);

$parametersNamed = $request[$operationRequestWrapper];
$parametersPositional = array_values($parametersNamed);

$result = $operation(...$parametersPositional);

$_format = isset($argv[1]) ? $argv[1] : 'json';

$responseBody = "";
if ($_format == 'xml') {
    $responseBody .= <<<XML
<${operation}Response>
    <${operation}Result>$result</${operation}Result>
</${operation}Response>

XML;
// Exception/Error in most languages have a 'message', SoapFault had 'faultstring' or 'reasonText', so just go for 'message'
// Exception/Error in most languages have no concept of "Server/Client" or "Sender/Receiver, we could call it "Caller/Callee" - or leave it out
// Exception/Error in most languages do not have a "code", make it optional/extendable
$responseBody .= <<<XML
<${operation}Response http:statusCode="404" http:reasonPhrase="Not Found">
    <prot:Fault message="Computation Failed" php:code="999">
        <prot:Detail>
            <myDetail in="${parametersPositional[0]}"/>
        </prot:Detail>
    </prot:Fault>
</${operation}Response>

XML;

} else {
    $responseBody .= json_encode([
        "${operation}Response" => [
            "${operation}Result" => $result,
        ]
    ], JSON_PRETTY_PRINT) . "\n";
    $responseBody .= json_encode([
        "${operation}Response" => [
            'Fault' => [
                'Detail' => [
                    'myDetail' => [
                        'in' => $parametersPositional[0],
                    ]
                ]
            ],
            'statusCode' => 404,
            'reasonPhrase' => 'Not Found',
        ]
    ], JSON_PRETTY_PRINT);
}

echo "$requestBody\n";
echo "\n";
echo "$responseBody\n";
