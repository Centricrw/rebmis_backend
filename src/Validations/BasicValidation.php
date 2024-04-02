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

    public static function validateTeacherChapterMarks($input, $keys)
    {
        if (!isset($input['courseNavigation']) || !is_numeric($input['courseNavigation'])) {
            return ["validated" => false, "message" => ""];
        }
        foreach ($keys as $key) {
            if (!isset($input[$key]) || !is_numeric($input[$key])) {
                return ["validated" => false, "message" => "$key is required and must be number!"];
            }
            if ($input[$key] < 0 || $input[$key] > 100) {
                return ["validated" => false, "message" => "$key must be between 0 nad 100!"];
            }
        }
        return ["validated" => true, "message" => "OK"];
        return true;
    }
}
