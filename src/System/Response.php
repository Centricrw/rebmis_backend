<?php
namespace Src\System;

class Response
{
    private int $statusCode;
    private string $body;

    public function __construct(int $statusCode, string $body = '')
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        echo json_encode($this->body);
    }
}
