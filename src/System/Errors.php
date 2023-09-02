<?php
namespace Src\System;

class Errors
{
    // public $statusCode;

    // public function __construct()
    // {
    //     $this->statusCode = array(
    //         "OK" => "HTTP/1.1 200 OK",
    //         "Created" => "HTTP/1.1 201 Created",
    //         "Accepted" => "HTTP/1.1 202 Accepted",
    //         "NoContent" => "HTTP/1.1 204 No Content",
    //         "MovedPermanently" => "HTTP/1.1 301 Moved Permanently",
    //         "BadRequest" => "HTTP/1.1 400 Bad Request",
    //         "Unauthorized" => "HTTP/1.1 401 Unauthorized",
    //         "Forbidden" => "HTTP/1.1 403 Forbidden",
    //         "NotFound" => "HTTP/1.1 404 Not Found",
    //         "Unprocessable" => "HTTP/1.1 422 Unprocessable Content",
    //         "Conflict" => "HTTP/1.1 409 Conflict",
    //         "ServerError" => "HTTP/1.1 500 Internal Server Error",
    //         "TooLarge" => "HTTP/1.1 413 Content Too Large",
    //     );
    // }
    public static function unprocessableEntityResponse($msg = "Invalid input, please try again!")
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Content';
        $response['body'] = json_encode([
            'message' => $msg,
        ]);
        return $response;
    }

    public static function badRequestError($msg = "Something went wrong, please try again!")
    {
        $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
        $response['body'] = json_encode(["message" => $msg]);
        return $response;
    }

    public static function notFoundError($msg)
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode(["message" => $msg]);
        return $response;
    }

    public static function notAuthorized()
    {
        $response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
        $response['body'] = json_encode(["message" => "Not Authorized"]);
        return $response;
    }

    public static function databaseError($stack = null)
    {
        $response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
        $response['body'] = json_encode(["message" => "Something went wrong, please try again!", "stack" => $stack]);
        return $response;
    }

    public static function existError($data)
    {
        $response['status_code_header'] = 'HTTP/1.1 403 Forbidden';
        $response['body'] = json_encode([
            'message' => $data,
        ]);
        return $response;
    }
}
