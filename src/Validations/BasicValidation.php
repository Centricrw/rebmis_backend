<?php
namespace Src\Validations;

class BasicValidation
{
    /**
     * @param string[]  $input
     * @param string[]  $keys
     *
     * @return array $response
     */
    public static function validate($input, $keys)
    {
        foreach ($keys as $key => $message) {
            if (!isset($input[$key]) || empty($input[$key])) {
                return ["validated" => false, "message" => $message];
            }
        }
        return ["validated" => true, "message" => "OK"];
        return true;
    }
}
