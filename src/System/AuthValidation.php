<?php
namespace Src\System;

use Firebase\JWT\Key;
use \Firebase\JWT\JWT;

class AuthValidation
{

    public static function encodeData($payload_info, $secret_key)
    {
        $jwt = JWT::encode($payload_info, $secret_key, 'HS512');
        return $jwt;
    }

    public static function isValidJwt($data)
    {
        try {
            $secret_key = "owt125";
            JWT::decode($data->jwt, new Key($secret_key, 'HS512'));
            return true;
        } catch (\Throwable $th) {
            return false;
        }

    }
    public static function decodedData($data)
    {
        $secret_key = "owt125";
        $decoded_data = JWT::decode($data->jwt, new Key($secret_key, 'HS512'));
        return $decoded_data;
    }

    public static function tokenName()
    {
        return "Coder-Token";
    }
    public static function authorized()
    {
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();

        if (isset($all_headers['Tmis-Token'])) {
            $jwt_data->jwt = $all_headers['Tmis-Token'];
        }
        // Decoding jwt
        if (empty($jwt_data->jwt)) {
            header("HTTP/1.1 403 Forbidden");
            $message = json_encode([
                "message" => "Not Authorized!",
            ]);
            exit($message);
        }

        if (!AuthValidation::isValidJwt($jwt_data)) {
            header("HTTP/1.1 403 Forbidden");
            $message = json_encode([
                "message" => "Not Authorized!",
            ]);
            exit($message);
        }

        $decoded_data = AuthValidation::decodedData($jwt_data);
        $decoded_data->data->jwt = $jwt_data->jwt;

        return $decoded_data->data;
    }
}
