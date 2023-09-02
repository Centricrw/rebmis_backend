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
    public static function ValidateNewInsertedUser($input)
    {
        if (empty($input['nid']) || !preg_match('/^[0-9]{16}$/', $input['nid'])) {
            return ["validated" => false, "message" => "Invalid nid or not provided!, please try again"];
        }
        if (empty($input['first_name'])) {
            return ["validated" => false, "message" => "First name not provided!"];
        }
        if (empty($input['last_name'])) {
            return ["validated" => false, "message" => "Last name not provided!"];
        }
        if (empty($input['full_name'])) {
            return ["validated" => false, "message" => "Full name not provided!"];
        }
        if (empty($input['resident_district_id'])) {
            return ["validated" => false, "message" => "Resident district id not provided!"];
        }
        if (empty($input['gender']) || ($input['gender'] != "FEMALE" && $input['gender'] != "MALE")) {
            return ["validated" => false, "message" => "Gender must be FEMALE, MALE or Other and required!"];
        }
        if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            return ["validated" => false, "message" => "Invalid Email or not provided!, please try again?"];
        }
        if (empty($input['phone_numbers']) || !preg_match('/^0[7][0-9]{8}$/', $input['phone_numbers'])) {
            return ["validated" => false, "message" => "Inavalid phone number or not provided!, please try again?"];
        }
        return ["validated" => true, "message" => "OK"];
    }
    public static function updateUser($input)
    {
        if (empty($input['nid'])) {
            return false;
        }
        return true;
    }
}
