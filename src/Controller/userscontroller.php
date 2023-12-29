<?php
namespace Src\Controller;

use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Src\Models\AuthModel;
use Src\Models\BanksModel;
use Src\Models\CountriesModel;
use Src\Models\EducationDomainModel;
use Src\Models\EducationSubdomainModel;
use Src\Models\MartialStatusModel;
use Src\Models\PostsModel;
use Src\Models\SpecializationsModel;
use Src\Models\StaffCategoryModel;
use Src\Models\TempTeachersModel;
use Src\Models\UserRoleModel;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;
use Src\Validations\BasicValidation;
use Src\Validations\UserValidation;
use \Firebase\JWT\JWT;

class UsersController
{
    private $db;
    private $usersModel;
    private $authModel;
    private $tempTeachersModel;
    private $postsModel;
    private $userRoleModel;
    private $martialStatusModel;
    private $banksModel;
    private $countriesModel;
    private $educationDomainModel;
    private $educationSubdomainModel;
    private $staffCategoryModel;
    private $specializationsModel;
    private $request_method;
    private $params;
    private $reader;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->usersModel = new UsersModel($db);
        $this->authModel = new AuthModel($db);
        $this->tempTeachersModel = new TempTeachersModel($db);
        $this->postsModel = new PostsModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->martialStatusModel = new MartialStatusModel($db);
        $this->countriesModel = new CountriesModel($db);
        $this->banksModel = new BanksModel($db);
        $this->educationDomainModel = new EducationDomainModel($db);
        $this->educationSubdomainModel = new EducationSubdomainModel($db);
        $this->staffCategoryModel = new StaffCategoryModel($db);
        $this->specializationsModel = new SpecializationsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0) {
                    if (isset($this->params['id'])) {
                        $response = $this->getUser($this->params['id']); //
                    } elseif (isset($this->params['page'])) {
                        $response = $this->getUsersByRole($this->params['role_id'], $this->params['page']);
                    } elseif (isset($this->params['action']) && $this->params['action'] === "profile") {
                        $response = $this->getUserByEmailPhoneNumberNid($this->params['user_id']);
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                } else {
                    $response = Errors::notFoundError("Route not found!");
                }
                break;
            case "POST":
                $response = $this->assignUserSchools();
                break;
            case 'PATCH':
                if ($this->params['action'] == "suspend") {
                    $response = $this->suspendUser($this->params['user_id']);
                } elseif ($this->params['action'] == "activate") {
                    $response = $this->activateUser($this->params['user_id']);
                } elseif ($this->params['action'] == "status") {
                    $response = $this->changeStatusUserToRoleHandler($this->params['user_id']);
                } elseif ($this->params['action'] == "assignaccess") {
                    $response = $this->assignAccessToUser($this->params['user_id']);
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
            echo $response['body'];
        }
    }

    function assignUserSchools()
    {

        $input = (array) json_decode(file_get_contents('php://input'), true);

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
        // Generate user id
        $role_to_user_id = UuidGenerator::gUuid();

        $created_by = AuthValidation::decodedData($jwt_data)->data->id;

        // Validate input if not empty
        // validation
        $validateUserInputData = UserValidation::assignUserToSchool($input);
        if (!$validateUserInputData['validated']) {
            return Errors::unprocessableEntityResponse($validateUserInputData['message']);
        }
        $input['role_to_user_id'] = $role_to_user_id;

        // Disable current role
        $this->userRoleModel->disableRole($input['user_id'], $created_by, "Active", "Disabled");
        // Assign new role
        $this->userRoleModel->insertIntoUserToRole($input, $created_by);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            "message" => "Role assigned to user!",
        ]);
        return $response;
    }

    function uploadTeachers()
    {

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
        if (!isset($_POST['district_code']) && empty($_POST['district_code'])) {
            return Errors::notFoundError("District code is required");
        }
        if (!isset($_POST['school_code']) && empty($_POST['school_code'])) {
            return Errors::notFoundError("School code is required");
        }
        if (!isset($_POST['qualification_id']) && empty($_POST['qualification_id'])) {
            return Errors::notFoundError("Qualification code is required");
        }
        if (!isset($_POST['academic_year_id']) && empty($_POST['academic_year_id'])) {
            return Errors::notFoundError("Academic_year is required");
        }

        $district_code = $_POST['district_code'];
        $school_code = $_POST['school_code'];
        $qualification_id = $_POST['qualification_id'];
        $academic_year_id = $_POST['academic_year_id'];

        // (B) PHPSPREADSHEET TO LOAD EXCEL FILE
        if (isset($_FILES['import_file'])) {
            $allowed_ext = ['xls', 'csv', 'xlsx'];
            $file_name = $_FILES['import_file']['name'];
            $checking = explode(".", $file_name);
            $file_ext = end($checking);

            if (in_array($file_ext, $allowed_ext)) {
                $target_path = $_FILES['import_file']['tmp_name'];
                /** Load $inputFileName to a Spreadsheet object **/
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($target_path);
                $data = $spreadsheet->getActiveSheet()->toArray();
                $isheader = 0;
                // Empty excl
                if (count($data) < 1) {
                    return Errors::notFoundError("File must not be empty");
                }
                $allowed = $this->postsModel->allowedTeacherByQualification($district_code, $school_code, $qualification_id, $academic_year_id);

                if (count($allowed) <= 0) {
                    return Errors::notFoundError("District does not have requests");
                }

                // get existing teachers
                $existing_teachers = 0;
                // get allowed school teachers
                $allowed_school_teachers = $allowed[0]['dde_post_distribution'];
                $remaining_to_place = $allowed_school_teachers - $existing_teachers;
                $response['status_code_header'] = 'HTTP/1.1 400 OK';
                $response['body'] = json_encode([
                    "message" => $allowed_school_teachers,
                ]);
                return $response;
                if ($remaining_to_place < count($data)) {
                    return Errors::notFoundError("You trying to place large number than the school allowed teachers");
                }

                foreach ($data as $value) {
                    if ($isheader > 0) {
                        $this->tempTeachersModel->insert($value[6], $value[1], $value[2], $value[3], $value[4], $value[5], $user_id);
                    } else {
                        $isheader = 1;
                    }
                }

            } else {
                $response['status_code_header'] = 'HTTP/1.1 400 OK';
                $response['body'] = json_encode([
                    "message" => "Invalid format",
                ]);
                return $response;
            }
        }
        $response['status_code_header'] = 'HTTP/1.1 400 OK';
        $response['body'] = json_encode([
            "message" => "Imported",
        ]);
        return $response;
    }

    // Assign access to user
    function assignAccessToUser($user_id)
    {
        try {
            $data = (array) json_decode(file_get_contents('php://input'), true);
            // geting authorized user id
            $created_by_user_id = AuthValidation::authorized()->id;

            // validation
            $validateUserInputData = UserValidation::assignUserToSchool($data);
            if (!$validateUserInputData['validated']) {
                return Errors::unprocessableEntityResponse($validateUserInputData['message']);
            }

            // Check if user is registered
            $user = $this->usersModel->findOneUser($user_id, 1);
            if (sizeof($user) == 0) {
                return Errors::notFoundError("User not found!, please try again?");
            }

            // check if login is the same getting accesse
            if ($created_by_user_id == $user_id) {
                return Errors::badRequestError("You can not give yourself access!, please contact administrator?");
            }

            // Generate user id
            $role_to_user_id = UuidGenerator::gUuid();

            // check if user already have access role
            $userHasActiveRole = $this->userRoleModel->findCurrentUserRole($user_id);
            if (sizeof($userHasActiveRole) > 0) {
                //* Disable user to role
                $this->userRoleModel->disableRole($user_id, $created_by_user_id, "Active", "TRANSFERD");
            }

            $data['role_to_user_id'] = $role_to_user_id;
            $data['user_id'] = $user_id;
            $this->userRoleModel->insertIntoUserToRole($data, $created_by_user_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                "message" => "Change updated!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    // Get all users
    function getUsersByRole($role_id, $page = 1)
    {
        $result = $this->usersModel->findUsersByRole($role_id, $page);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    function getUserByEmailPhoneNumberNid($params)
    {

        try {
            $result = $this->usersModel->findExistPhoneNumberEmailNid($params, $params, $params, $params);
            if (sizeof($result) > 0) {
                unset($result[0]['password']);
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }
    // Get a user by id
    function getUser($params)
    {

        $result = $this->usersModel->findOneUser($params);
        if (sizeof($result) > 0) {
            unset($result[0]['password']);
            $eddomain = $this->educationDomainModel->findById($result[0]['education_domain_id']);
            $result[0]['education_domain_id'] = sizeof($eddomain) > 0 ? $eddomain[0]['education_domain_name'] : null;
            $edsudomain = $this->educationSubdomainModel->findById($result[0]['education_sub_dommain_id']);
            $result[0]['education_sub_dommain_id'] = sizeof($edsudomain) > 0 ? $edsudomain[0]['education_sub_dommain_id'] : null;
        }
        $result = sizeof($result) > 0 ? $result[0] : null;

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    // Suspend a user by id
    function suspendUser($user_id)
    {
        $data = new \stdClass();

        $all_headers = getallheaders();

        $data->jwt = $all_headers['Authorization'];

        // Decoding jwt
        if (empty($data->jwt)) {
            return Errors::notAuthorized();
        }

        try {
            $secret_key = "owt125";
            $decoded_data = JWT::decode($data->jwt, new Key($secret_key, 'HS512'));

            $user = $this->usersModel->findOneUser($user_id);
            if (sizeof($user) == 0) {
                return Errors::notFoundError("Action failed, you can not suspend this user is already suspended!");
            }

            $this->usersModel->changeStatus($user[0]['user_id'], $decoded_data->data->id, 0);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(["message" => "Account Suspended!"]);
            return $response;
        } catch (\Throwable $e) {
            return Errors::notAuthorized();
        }
    }

    /**
     * checking user status
     * @param string $user_id
     * @return array $response
     */
    function changeStatusUserToRoleHandler($user_id)
    {
        try {
            $data = (array) json_decode(file_get_contents('php://input'), true);
            // validation
            $validateThisValues = [
                "current_status" => "Current status is required",
                "new_status" => "New Status is required",
            ];
            $validateUserInputData = BasicValidation::validate($data, $validateThisValues);
            if (!$validateUserInputData['validated']) {
                return Errors::unprocessableEntityResponse($validateUserInputData['message']);
            }

            // checking if not === 'New','Active','Disabled','TRANSFERD','TERMINATED','SUSPENDED','Upgraded'
            $validStatus = ['New', 'Active', 'Disabled', 'TRANSFERD', 'TERMINATED', 'SUSPENDED', 'Upgraded'];
            if (!in_array($data["new_status"], $validStatus)) {
                return Errors::unprocessableEntityResponse("New status has invalid status, must be one this New, Active, Disabled, TRANSFERD, TERMINATED, SUSPENDED and Upgraded");
            }

            if (!in_array($data["current_status"], $validStatus)) {
                return Errors::unprocessableEntityResponse("Current status has invalid status, must be one this New, Active, Disabled, TRANSFERD, TERMINATED, SUSPENDED and Upgraded");
            }

            // geting authorized user id
            $logged_user_id = AuthValidation::authorized()->id;

            $user = $this->usersModel->findOneUser($user_id);
            if (sizeof($user) == 0) {
                return Errors::notFoundError("Action failed, you can not suspend this user is already suspended!");
            }

            $this->userRoleModel->updateUserToRoleStatus($user_id, $logged_user_id, $data['current_status'], $data["new_status"]);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(["message" => "User Status updated successfuly!"]);
            return $response;
        } catch (\Throwable $e) {
            return Errors::databaseError($e->getMessage());
        }
    }

    // Suspend a user by id
    function activateUser($user_id)
    {
        $data = new \stdClass();

        $all_headers = getallheaders();
        $data->jwt = $all_headers['Authorization'];

        // Decoding jwt
        if (empty($data->jwt)) {
            return Errors::notAuthorized();
        }
        try {
            $secret_key = "owt125";
            $decoded_data = JWT::decode($data->jwt, new Key($secret_key, 'HS512'));

            $user = $this->usersModel->findOneUser($user_id, 0);
            if (sizeof($user) == 0) {
                return Errors::notFoundError("Action failed, you can not activate this user is already activated!");
            }
            $this->usersModel->changeStatus($user[0]['user_id'], $decoded_data->data->id, 1);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(["message" => "Account Activated!"]);
            return $response;
        } catch (\Throwable $e) {
            return Errors::notAuthorized();
        }
    }
}
$controller = new UsersController($this->db, $request_method, $params);
$controller->processRequest();
