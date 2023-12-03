<?php
namespace Src\Controller;

use Src\Models\AuthModel;
use Src\Models\CohortconditionModel;
use Src\Models\RolesModel;
use Src\Models\SchoolLocationsModel;
use Src\Models\SchoolsModel;
use Src\Models\SectorsModel;
use Src\Models\StakeholdersModel;
use Src\Models\UserRoleModel;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Encrypt;
use Src\System\Errors;
use Src\System\InvalidDataException;
use Src\System\UuidGenerator;
use Src\Validations\UserValidation;

class AuthController
{
    private $db;
    private $usersModel;
    private $authModel;
    private $rolesModel;
    private $userRoleModel;
    private $schoolLocationsModel;
    private $schoolsModel;
    private $sectorsModel;
    private $stakeholdersModel;
    private $request_method;
    private $params;
    private $cohortconditionModel;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->authModel = new AuthModel($db);
        $this->usersModel = new UsersModel($db);
        $this->rolesModel = new RolesModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->schoolLocationsModel = new SchoolLocationsModel($db);
        $this->schoolsModel = new SchoolsModel($db);
        $this->sectorsModel = new SectorsModel($db);
        $this->stakeholdersModel = new StakeholdersModel($db);
        $this->cohortconditionModel = new CohortconditionModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) == 1) {
                    $response = $this->getUser($this->params['phone']);
                } else {
                    $response = $this->getCurrentUser();
                }
                break;
            case 'POST':
                if ($this->params['action'] == "create") {
                    $response = $this->createAccount();
                } elseif ($this->params['action'] == "login") {
                    $response = $this->login();
                } else {
                    $response = Errors::notFoundError("Route not found!");
                }
                break;
            case 'PATCH':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "password") {
                        $response = $this->login();
                    } elseif ($this->params['action'] == "profile") {
                        $response = $this->updateAccount($this->params['user_id']);
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                } else {
                    $response = Errors::notFoundError("Route not found!");
                }
                break;
            case 'DELETE':
                break;
            default:
                $response = Errors::notFoundError("Route not found!");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }
    /**
     * Validate imported teachers
     * @param string $nid
     * @param string $phoneNumber
     * @param string $userName
     * @param string $email
     * @throws InvalidDataException
     */
    function checkingIfUserNameNidPhoneNumberEmailExists($nid, $phoneNumber, $userName, $email)
    {
        // Check if user phone number, email, nid exists
        $emailExists = $this->usersModel->findExistEmailShort($email);
        if (sizeof($emailExists) > 0) {
            throw new InvalidDataException("Email already exist, please try again?");
        }
        // Check if user phone number, email, nid exists
        $phoneNumberExists = $this->usersModel->findExistPhoneNumberShort($phoneNumber);
        if (sizeof($phoneNumberExists) > 0) {
            throw new InvalidDataException("Phone number already exist, please try again?");
        }
        // Check if user phone number, email, nid exists
        $nidExists = $this->usersModel->findExistNidShort($nid);
        if (sizeof($nidExists) > 0) {
            throw new InvalidDataException("NID already exist, please try again?");
        }
        // Check if user phone number, email, nid exists
        $userNameExists = $this->usersModel->findByUsername($userName);
        if (sizeof($userNameExists) > 0) {
            throw new InvalidDataException("Username already exist, please try again?");
        }
    }

    /**
     * add tot training handler
     * @param object $data
     * @throws InvalidDataException
     */
    function addNewUserToTraininghandler($data)
    {
        // checking if cohortId is provided
        if (!isset($data["cohortId"]) || empty($data["cohortId"])) {
            throw new InvalidDataException("Cohort Id is required, please try again?");
        }

        // checking if training is provided
        if (!isset($data["trainingId"]) || empty($data["trainingId"])) {
            throw new InvalidDataException("Training Id is required, please try again?");
        }

        // checking if cohort condition Id is provided
        if (!isset($data["cohortconditionId"]) || empty($data["cohortconditionId"])) {
            throw new InvalidDataException("Cohort Condition Id is required, please try again?");
        }

        // checking if school code is provided
        if (!isset($data["school_code"]) || empty($data["school_code"])) {
            throw new InvalidDataException("School code is required, please try again?");
        }

        // get cohort condition details
        $conditionDetails = $this->cohortconditionModel->selectCohortConditionById($data['cohortconditionId']);
        //count avaible traineers
        $availableTraineers = $this->cohortconditionModel->countTraineersOnCondition($data);
        if (sizeof($availableTraineers) == (int) $conditionDetails[0]['capacity']) {
            throw new InvalidDataException("Needed Traineers completed!");
        }
    }

    function createAccount()
    {

        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $created_by_user_id = AuthValidation::authorized()->id;
        $validateUserInputData = UserValidation::ValidateNewInsertedUser($data);
        if (!$validateUserInputData['validated']) {
            return Errors::unprocessableEntityResponse($validateUserInputData['message']);
        }

        try {

            // Check if user phone number, username , email, nid exists
            $username = isset($data["username"]) && !empty($data["username"]) ? $data["username"] : $data["phone_numbers"];
            $this->checkingIfUserNameNidPhoneNumberEmailExists($data["nid"], $data["phone_numbers"], $username, $data["email"]);

            // checking is user training data completed
            if (isset($data["addToTraining"]) && $data["addToTraining"]) {
                $this->addNewUserToTraininghandler($data);
            }

            // checkking if staff_code exists
            if (isset($data["staff_code"]) && !empty($data['staff_code'])) {
                $userStaffCodeExist = $this->usersModel->findUserByStaffcode($data["staff_code"]);
                if (sizeof($userStaffCodeExist) > 0) {
                    return Errors::notFoundError("User staff code already exists!, please try again?");
                }
            }

            // Encrypting default password
            $default_password = 12345;
            $default_password = Encrypt::saltEncryption($default_password);

            // Generate user id
            $user_id = UuidGenerator::gUuid();

            $data['password'] = $default_password;
            $data['user_id'] = $user_id;
            $data['created_by'] = $created_by_user_id;

            $results = $this->usersModel->insertNewUser($data);

            // insert user to training
            if ($data["addToTraining"]) {
                // Generate traineer id
                $generated_traineer_id = UuidGenerator::gUuid();
                $data['traineesId'] = $generated_traineer_id;
                $data['traineePhone'] = $data["phone_numbers"];
                $this->cohortconditionModel->InsertApprovedSelectedTraineers($data, $created_by_user_id);
            }

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                'message' => "Created",
                'user_id' => $user_id,
                'traineesId' => isset($data['traineesId']) ? $data['traineesId'] : null,
                'results' => $results,
            ]);
            return $response;
        } catch (InvalidDataException $e) {
            return Errors::existError($e->getMessage());
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    function updateAccount($user_id)
    {
        $rlt = new \stdClass();
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if (isset($all_headers['Authorization'])) {
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if (empty($jwt_data->jwt)) {
            return Errors::notAuthorized();
        }

        if (!AuthValidation::isValidJwt($jwt_data)) {
            return Errors::notAuthorized();
        }
        $updated_by = AuthValidation::decodedData($jwt_data)->data->id;

        $data = (array) json_decode(file_get_contents('php://input'), true);

        if (!UserValidation::updateUser($data)) {
            return Errors::unprocessableEntityResponse();
        }

        // // Check if user is registered
        $user = $this->usersModel->findOneUser($user_id, 1);
        if (sizeof($user) <= 0) {
            return Errors::notFoundError("User not found");
        }

        // Check if user is registered
        if (empty($user[0]['username']) && empty($data['username'])) {
            // Check if username is registered
            // $user = $this->usersModel->findExistUserName($data['username'], $user_id, 1);
            // if (sizeof($user) > 0) {
            //     return Errors::ExistError("Username is already exist");
            // }
            // Encrypting default password
            $default_password = 12345;
            $default_password = Encrypt::saltEncryption($default_password);
            $data['password'] = $default_password;

            $this->usersModel->changeUsernameAndPassword($data, $user_id, $updated_by);
        }

        $this->usersModel->updateUser($data, $user_id, $updated_by);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode([
            'message' => "Updated",
        ]);
        return $response;
    }

    // Get all users
    function getCurrentUser()
    {

        $rlt = new \stdClass();
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if (isset($all_headers['Authorization'])) {
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if (empty($jwt_data->jwt)) {
            return Errors::notAuthorized();
        }

        if (!AuthValidation::isValidJwt($jwt_data)) {
            return Errors::notAuthorized();
        }
        $user_id = AuthValidation::decodedData($jwt_data)->data->id;

        $result = $this->usersModel->findOneUser($user_id, 1);
        if (sizeof($result) > 0) {

            $rlt->jwt = $jwt_data->jwt;
            $rlt->user_info = $result[0];
            $user_role = $this->userRoleModel->findCurrentUserRole($result[0]['user_id']);

            // Role to user

            if (sizeof($user_role) > 0) {
                $role = $this->rolesModel->findById($user_role[0]['role_id']);
                $rlt->role = $role[0];
                if ($user_role[0]['country_id'] != null) {
                    $rlt->country = $user_role[0]['country_id'];
                } else {
                    $rlt->country = null;
                }
                if ($user_role[0]['district_code'] != null) {
                    $district = $this->schoolLocationsModel->findDistrictByCode($user_role[0]['district_code']);
                    $rlt->district = $district[0];
                } else {
                    $rlt->district = null;
                }
                if ($user_role[0]['sector_code'] != null) {
                    $sector = $this->schoolLocationsModel->findSectorByCoder($user_role[0]['sector_code']);
                    $rlt->sector = $sector[0];
                } else {
                    $rlt->sector = null;
                }
                if ($user_role[0]['school_code'] != null) {
                    $school = $this->schoolsModel->findByCode($user_role[0]['school_code']);
                    $rlt->school = $school[0];
                } else {
                    $rlt->school = null;
                }
                if ($user_role[0]['stakeholder_id'] != null) {
                    $stakeholder = $this->stakeholdersModel->findByCode($user_role[0]['stakeholder_id']);
                    $rlt->stakeholder = $stakeholder[0];
                } else {
                    $rlt->stakeholder = null;
                }
            }
        } else {
            $rlt = null;
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($rlt);
        return $response;

    }
    // Get a user by id
    function login()
    {
        $input = (array) json_decode(file_get_contents('php://input'), true);
        // Validate input if not empty
        if (!self::validateCredential($input)) {
            return Errors::unprocessableEntityResponse();
        }
        $userAuthData = $this->usersModel->findByUsername($input['username']);
        if (sizeof($userAuthData) == 0) {
            $userAuthData = $this->usersModel->findUserByPhoneNumber($input['username']);
            if (sizeof($userAuthData) == 0) {
                $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
                $response['body'] = json_encode([
                    'message' => "Wrong username or password, Please try again?",
                ]);
                return $response;
            }
        }
        $input_password = Encrypt::saltEncryption($input['password']);
        // Password compare
        if ($input_password !== $userAuthData[0]['password']) {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
            $response['body'] = json_encode([
                'message' => "Username/password does not match",
            ]);
            return $response;
        }

        $userInfo = $this->usersModel->findOneUser($userAuthData[0]['user_id'], 1);

        $iss = "localhost";
        $iat = time();
        $eat = $iat + 21600;
        $aud = "myusers";
        $user_array_data = array(
            "id" => $userInfo[0]['user_id'],
            "username" => $userInfo[0]['username'],
            "email" => $userInfo[0]['email'],
        );

        $secret_key = "owt125";
        $payload_info = array(
            "iss" => $iss,
            "iat" => $iat,
            "eat" => $eat,
            "aud" => $aud,
            "data" => $user_array_data,
        );

        $jwt = AuthValidation::encodeData($payload_info, $secret_key);

        $rlt = new \stdClass();

        $rlt->jwt = $jwt;
        $rlt->user_info = sizeof($userInfo) > 0 ? $userInfo[0] : null;

        $user_role = $this->userRoleModel->findCurrentUserRole($userInfo[0]['user_id']);

        if (sizeof($user_role) > 0) {
            $role = $this->rolesModel->findById($user_role[0]['role_id']);
            $rlt->role = $role[0];
            if ($user_role[0]['country_id'] != null) {
                $rlt->country = $user_role[0]['country_id'];
            } else {
                $rlt->country = null;
            }
            if ($user_role[0]['district_code'] != null) {
                $district = $this->schoolLocationsModel->findDistrictByCode($user_role[0]['district_code']);
                $rlt->district = $district[0];
            } else {
                $rlt->district = null;
            }
            if ($user_role[0]['sector_code'] != null) {
                $sector = $this->schoolLocationsModel->findSectorByCoder($user_role[0]['sector_code']);
                $rlt->sector = $sector[0];
            } else {
                $rlt->sector = null;
            }
            if ($user_role[0]['school_code'] != null) {
                $school = $this->schoolsModel->findByCode($user_role[0]['school_code']);
                $rlt->school = $school[0];
            } else {
                $rlt->school = null;
            }
            if ($user_role[0]['stakeholder_id'] != null) {
                $stakeholder = $this->stakeholdersModel->findByCode($user_role[0]['stakeholder_id']);
                $rlt->stakeholder = $stakeholder[0];
            } else {
                $rlt->stakeholder = null;
            }
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($rlt);

        return $response;
    }
    // Get all user by username
    // Get a user by id
    function getUser($params)
    {

        $result = $this->usersModel->findOneUser($params);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    private function validateCredential($input)
    {
        if (empty($input['username'])) {
            return false;
        }
        if (empty($input['password'])) {
            return false;
        }
        return true;
    }

    private function validateAccountData($input)
    {
        if (empty($input['first_name'])) {
            return false;
        }
        if (empty($input['last_name'])) {
            return false;
        }
        if (empty($input['phone_numbers'])) {
            return false;
        }
        if (empty($input['email'])) {
            return false;
        }
        if (empty($input['role_id'])) {
            return false;
        }
        if (empty($input['username'])) {
            return false;
        }
        return true;
    }
}
$controller = new AuthController($this->db, $request_method, $params);
$controller->processRequest();
