<?php
namespace Src\System;

class Errors {

    public static function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 400 Unprocessable Entity';
        $response['body'] = json_encode([
            'message' => 'Invalid input'
        ]);
        return $response;
    }
    public static function badRequestError()
    {
        $response['status_code_header'] = 'HTTP/1.1 500 Not Found';
        $response['body'] = json_encode(["message" =>"Body request"]);
        return $response;
    }
    public static function notFoundError($msg)
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode(["message" =>$msg]);
        return $response;
    }
    public static function notAuthorized()
    {
        $response['status_code_header'] = 'HTTP/1.1 401 Not Found';
        $response['body'] = json_encode(["message" =>"Not Authorized"]);
        return $response;
    }
    public static function databaseError()
    {
        $response['status_code_header'] = 'HTTP/1.1 401 Not Found';
        $response['body'] = json_encode(["message" =>"Not Authorized"]);
        return $response;
    }
    public static function existError($data){
        $response['status_code_header'] = 'HTTP/1.1 403 Already exist';
        $response['body'] = json_encode([
        'message' => $data
        ]);
        return $response;
    }
}
?>