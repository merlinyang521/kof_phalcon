<?php
namespace Kof\Phalcon\Http;

use Phalcon\Http\Response as PhalconHttpResponse;
use Psr\Http\Message\ResponseInterface;

class Response extends PhalconHttpResponse
{
    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function setPsr7Response(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $headerName => $headers) {
            foreach ($headers as $header) {
                $this->setHeader($headerName, $header);
            }
        }
        $this->setContent($response->getBody()->__toString());
        $this->setStatusCode($response->getStatusCode(), $response->getReasonPhrase());

        return $this;
    }
}
