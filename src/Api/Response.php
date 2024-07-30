<?php

namespace UnasOnline\UnasConnect\Api;

use UnasOnline\UnasConnect\Utils\Xml;

class Response
{
    private int $statusCode;
    private string $content;
    private string $error;
    
    public function __construct(int $statusCode, string|bool $content, string $error)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->error = $error;
    }

    /**
     * Return api response body as a string
     *
     * @return string
     */
    public function getResponseContent(): string
    {
        return $this->content;
    }

    /**
     * Return api response body as an array
     *
     * @return array
     */
    public function getResponse(): array
    {
        if (empty($this->content)) {
            return [];
        }

        return Xml::simpleXmlToArray(simplexml_load_string($this->content, 'SimpleXMLElement', LIBXML_NOCDATA));
    }

    /**
     * Return http status code
     *
     * @return int status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Return api error as a string, if any
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
