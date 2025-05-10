<?php

namespace App\Utils;

class Request
{
    private $headers;
    private $queryParams;
    private $bodyParams;
    private $pathParams;

    public function __construct()
    {
        $this->headers = getallheaders();
        $this->queryParams = $_GET;
        $this->bodyParams = $this->parseBody();
        $this->pathParams = [];
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function header($name, $default = null)
    {
        return $this->headers[$name] ?? $default;
    }

    public function getQuery()
    {
        return $this->queryParams;
    }

    public function getQueryParam($name, $default = null)
    {
        return $this->queryParams[$name] ?? $default;
    }

    public function getBody()
    {
        return $this->bodyParams;
    }

    public function getBodyParam($name, $default = null)
    {
        return $this->bodyParams[$name] ?? $default;
    }

    public function setPathParams(array $params)
    {
        $this->pathParams = $params;
    }

    public function getPathParam($name, $default = null)
    {
        return $this->pathParams[$name] ?? $default;
    }

    public function getPath()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    private function parseBody()
    {
        $contentType = $this->header('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return [];
    }
}
