<?php
namespace Src\Controller;

use Src\Models\ChangePasswordModel;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\DatabaseConnector;
use Src\System\Encrypt;
use Src\System\Errors;

class ChangePasswordController
{
    private $db;
    private $usersModel;
    private $request_method;
    private $params;
    private $closeConnection;
    private $changePasswordModel;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->usersModel = new UsersModel($db);
        $this->changePasswordModel = new ChangePasswordModel($db);
        $this->closeConnection = new DatabaseConnector();
    }

    public function processRequest()
    {
        switch ($this->request_method) {
            case 'POST':
                if ($this->params['action'] == "new") {
                    $response = $this->createtNewPassword();
                } else if ($this->params['action'] == "forget") {
                    $response = $this->generatePasswordVerficationCode();
                } else if ($this->params['action'] == "verify") {
                    $response = $this->changeForgettenPassword();
                } else if ($this->params['action'] == "assign") {
                    $response = Errors::notFoundError("Route in Development!");
                } else if ($this->params['action'] == "unassign") {
                    $response = Errors::notFoundError("Route in Development!");
                } else {
                    $response = Errors::notFoundError("Route not found!");
                }
                break;
            default:
                $response = Errors::notFoundError("Route not found!");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            $this->closeConnection->closeConnection();
            echo $response['body'];
        }
    }

    /**
     * function to create new password
     * check authantication
     * verfy if every input is not empty
     * get user info
     * compare password
     * Encrypt new password
     * save new password
     */

    public function createtNewPassword()
    {
        // getting input data
        $input = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        // checking if validation of current_password is  avilable
        if (empty($input['current_password'])) {
            return Errors::badRequestError("current_password is not provided!");
        }
        // checking if validation of new_password is  avilable
        if (empty($input['new_password'])) {
            return Errors::badRequestError("new_password is not provided!");
        }
        // finding user info
        $userInfo = $this->usersModel->findOneUser($logged_user_id);
        if (sizeof($userInfo) == 0) {
            return Errors::badRequestError("logged user not found!, please try again?");
        }
        // Password compare
        $currentPassword = Encrypt::saltEncryption($input['current_password']);
        //
        if ($currentPassword !== $userInfo[0]['password']) {
            return Errors::badRequestError("User password does not match");
        }

        try {
            // Encrypting new password
            $newPassword = $input['new_password'];
            $encryptedNewPassword = Encrypt::saltEncryption($newPassword);

            // updating password
            $data['username'] = $userInfo[0]['username'];
            $data['password'] = $encryptedNewPassword;
            $this->usersModel->changeUsernameAndPassword($data, $logged_user_id, $logged_user_id);

            $response['status_code_header'] = 'HTTP/1.1 200 Ok';
            $response['body'] = json_encode([
                'message' => "Password Changed Successfuly!",
                "results" => $userInfo[0],
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Function to generate random string
     */
    public function generateRandomString($length = 6)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Generate 6 random number
     * send it to phone number
     * and then save it to database
     */
    public function generatePasswordVerficationCode()
    {
        // getting input data
        $input = (array) json_decode(file_get_contents('php://input'), true);

        // checking if validation of phone_number or email is  avilable
        if (empty($input['phone_number'])) {
            return Errors::badRequestError("Phone number or email is not provided!");
        }
        // checking if provided phone is avialable in database
        $userInfo = $this->usersModel->findExistPhoneNumberEmailNid($input['phone_number'], $input['phone_number'], $input['phone_number']);
        if (sizeof($userInfo) == 0) {
            return Errors::badRequestError("Email or Phone Number Not Found!, please contact administrator.");
        }
        try {
            // *getting generated string
            $verficationCode = $this->generateRandomString(6);
            // *send verfication code to provided phone number
            // $msg = "Your verification Code is: $verficationCode, Don't Share this code with anyone.\n(TMIS)";
            // $phoneNumber = $input['phone_number'];
            // $sendVerficationMessage = $this->changePasswordModel->sendVerficationCodeMessage($msg, $phoneNumber);
            // $resultsDecode = json_decode($sendVerficationMessage['result'], true);

            // // *checkin there is error in sending message
            // if ($resultsDecode["success"] == false || empty($resultsDecode["details"])) {
            //     return Errors::databaseError("Something Went Wrong, Failed to Send Message.");
            // }
            // *saving generated verfication code
            $this->changePasswordModel->upadetPasswordVerficstionCode($userInfo[0]['user_id'], $verficationCode);
            $response['status_code_header'] = 'HTTP/1.1 200 Ok';
            $response['body'] = json_encode([
                'message' => "Password Verfication Code Send Successfuly!",
                "User" => $input['phone_number'],
                "verficationCode" => $verficationCode,
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Change forgetten Password to new
     * check if verfication code is valid
     * Encrypt new password
     * save new password
     */
    public function changeForgettenPassword()
    {
        // getting input data
        $input = (array) json_decode(file_get_contents('php://input'), true);

        // checking if validation of verification_code is  avilable
        if (empty($input['verification_code'])) {
            return Errors::badRequestError("verification_code is not provided!");
        }

        // checking if validation of phone_number is  avialable
        if (empty($input['phone_number'])) {
            return Errors::badRequestError("phone_number or email is not provided!");
        }

        // checking if validation of newpassword is  avialable
        if (empty($input['new_password'])) {
            return Errors::badRequestError("new_password is not provided!");
        }

        // checking if provided phone is avialable in database
        $userInfo = $this->usersModel->findExistPhoneNumberEmailNid($input['phone_number'], $input['phone_number'], $input['phone_number']);
        if (sizeof($userInfo) == 0) {
            return Errors::badRequestError("Username or Phone Number Not Found!, please contact administrator.");
        }

        try {
            // checking if verfication is valid
            $verify = $this->changePasswordModel->getCurrentPasswordVerficstionCode($userInfo[0]['user_id'], $input['verification_code']);
            if (sizeof($verify) == 0) {
                return Errors::badRequestError("Verification code does not match!");
            }
            // Encrypting new password
            $newPassword = $input['new_password'];
            $encryptedNewPassword = Encrypt::saltEncryption($newPassword);

            // updating password
            $data['username'] = $userInfo[0]['username'];
            $data['password'] = $encryptedNewPassword;
            $this->usersModel->changeUsernameAndPassword($data, $userInfo[0]['user_id'], $userInfo[0]['user_id']);
            $response['status_code_header'] = 'HTTP/1.1 200 Ok';
            $response['body'] = json_encode([
                'message' => "Password Changed Successfuly!",
                "results" => $userInfo[0],
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}

$controller = new ChangePasswordController($this->db, $request_method, $params);
$controller->processRequest();
