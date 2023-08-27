<?php
namespace Src\Validations;

class UserValidation
{

    public static function assignUserToSchool($input)
    {
        if (empty($input['role_id'])) {
            return false;
        }
        if (empty($input['user_id'])) {
            return false;
        }
        return true;
    }
    public static function insertUser($input)
    {
        if (empty($input['nid'])) {
            return false;
        }
        return true;
    }
    /**
     * validating user information
     * @param {Json} $input
     * @return {Boolean} $results
     */
    public static function ValidateUpdatedUser($input)
    {
        if (empty($input['nid'])) {
            return false;
        }
        return true;
    }
}
