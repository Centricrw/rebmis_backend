<?php
namespace Src\Controller;

use Src\Models\BulkEnrollModel;
use Src\Models\CohortsModel;
use Src\Models\RolesModel;
use Src\Models\TeacherStudyHierarchyModel;
use Src\Models\UserRoleModel;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Encrypt;
use Src\System\Errors;
use Src\System\InvalidDataException;
use Src\System\UuidGenerator;

class bulkEnrollController
{
    private $db;
    private $bulkEnrollModel;
    private $request_method;
    private $params;
    private $usersModel;
    private $userRoleModel;
    private $rolesModel;
    private $cohortsModel;
    private $teacherStudyHierarchyModel;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->bulkEnrollModel = new BulkEnrollModel($db);
        $this->usersModel = new UsersModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->rolesModel = new RolesModel($db);
        $this->cohortsModel = new CohortsModel($db);
        $this->teacherStudyHierarchyModel = new TeacherStudyHierarchyModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'POST':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "bulkenroll") {
                        $response = $this->bulkEnroll();
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                }
                break;
            case 'GET':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "retrievedata") {
                        $response = $this->bulkEnrollRetrieveJson();
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                }
                break;
            case 'DELETE':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "deletejson") {
                        $response = $this->bulkEnrollDeleteJson();
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
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

    /**
     * Validate imported teachers
     * @param array $data
     * @throws InvalidDataException
     */
    private function bulkEnrollInputValidation(array $data)
    {
        foreach ($data as $key => $item) {
            // Validate gender
            if (!isset($item["gender"]) || !in_array($item["gender"], ['Gabo', 'Gore'])) {
                throw new InvalidDataException("On Index '$key' Gender must be 'Gabo' or 'Gore'");
            }

            // Validate grade
            if (!isset($item["grade"]) || !is_string($item["grade"]) || strlen($item["grade"]) < 2) {
                throw new InvalidDataException("On index '$key' Grade must be a string with a minimum of 2 characters");
            }

            // Validate email
            if (!isset($item["email"]) || !is_string($item["email"]) || !filter_var($item["email"], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidDataException("On index '$key' Email is not validated");
            }

            // Validate name
            if (!isset($item["name"]) || !is_string($item["name"]) || strlen($item["name"]) < 2) {
                throw new InvalidDataException("On index '$key' Name must be a string with a minimum of 2 characters");
            }

            // Validate nid
            if (!isset($item["nid"]) || !is_string($item["nid"]) || strlen($item["nid"]) != 16) {
                throw new InvalidDataException("On index '$key' NID must be a string with a maximum of 16 characters");
            }

            // Validate phone number
            if (!isset($item["phone_number"]) || !is_string($item["phone_number"]) || strlen($item["phone_number"]) != 10 || !preg_match('/^07/', $item["phone_number"])) {
                throw new InvalidDataException("On index '$key' Phone number must be a string starting with '07' and have 10 digits");
            }

            // Validate other keys
            $requiredKeys = ['qualification', 'school_code', 'staff_code'];

            foreach ($requiredKeys as $requiredKey) {
                if (!isset($item[$requiredKey]) || empty($item[$requiredKey])) {
                    throw new InvalidDataException("On index '$key' $requiredKey is either not set or empty");
                }
            }
        }
    }

    /**
     * create new user
     */
    function createNewUserHandler($userData, $created_by_user_id)
    {
        // data to be insterted
        $names = explode(" ", $userData["name"]);
        $insertedData = [
            "staff_code" => $userData["staff_code"],
            "full_name" => $userData["name"],
            "gender" => $userData["gender"] == "Gabo" ? "MALE" : "FEMALE",
            "nid" => $userData["nid"],
            "email" => $userData["email"],
            "phone_numbers" => $userData["phone_number"],
            "username" => $userData["phone_number"],
            "created_by" => $created_by_user_id,
            "first_name" => isset($names[0]) ? $names[0] : "",
            "middle_name" => isset($names[2]) ? $names[2] : "",
            "last_name" => isset($names[1]) ? $names[1] : "",
            "resident_district_id" => substr($userData["staff_code"], 0, 2),
        ];

        // checking if username Exists
        $userNameExists = $this->usersModel->findByUsernameShort($userData['phone_number']);
        if (sizeof($userNameExists) > 0) {
            //* update user
            $this->usersModel->updateUser($insertedData, $userNameExists[0]['user_id'], $created_by_user_id);
            return ["deplicate" => false, "user" => $userNameExists];
        }

        // Check if user already exists
        $existingUser = $this->usersModel->findOneUserShort($userData['staff_code'], $userData['phone_number']);
        if (sizeof($existingUser) > 0) {
            //* update user
            $this->usersModel->updateUser($insertedData, $existingUser[0]['user_id'], $created_by_user_id);
            return ["deplicate" => false, "user" => $existingUser];
        }

        // Check if user phone number, email, nid exists
        $phoneNumberExists = $this->usersModel->findExistPhoneNumberEmailNid($userData['phone_number'], $userData['email'], $userData['nid']);
        if (sizeof($phoneNumberExists) > 0) {
            // throw new InvalidDataException($userData['name'] . " has already exist Phone number, nid or email");
            return ["deplicate" => true, "user" => $userData];
        }

        // Encrypting default password
        $default_password = 12345;
        $default_password = Encrypt::saltEncryption($default_password);

        // Generate user id
        $user_id = UuidGenerator::gUuid();

        $insertedData['password'] = $default_password;
        $insertedData['user_id'] = $user_id;

        // inert new user
        $this->usersModel->insertNewUser($insertedData);
        return ["deplicate" => false, "user" => $insertedData];
    }

    /**
     * create userT0role
     */

    function createUserAccessToRole($data, $created_by_user_id, $user_id)
    {
        // Generate user id
        $role_to_user_id = UuidGenerator::gUuid();
        // checking if role exists
        $roleResults = $this->rolesModel->findRoleByName($data['role']);
        $dataToInsert = [
            "role_to_user_id" => $role_to_user_id,
            "user_id" => $user_id,
            "role_id" => isset($roleResults[0]['role_id']) ? $roleResults[0]['role_id'] : 1,
            "district_code" => substr($data["school_code"], 0, 2),
            "sector_code" => substr($data["school_code"], 0, 4),
            "school_code" => $data["school_code"],
            "created_by" => $created_by_user_id,
            "qualification_id" => ($data["qualification"] == "A0" ? "1" : $data["qualification"] == "A1") ? "2" : "3",
        ];
        // check if user already have access role
        $userHasActiveRole = $this->userRoleModel->findCurrentUserRoleShort($user_id);
        if (sizeof($userHasActiveRole) > 0) {
            //* Disable user to role
            $this->userRoleModel->disableRole($user_id, $created_by_user_id, "Active", "TRANSFERD");
        }
        // insert new access to user
        $this->userRoleModel->insertIntoUserToRole($dataToInsert, $created_by_user_id);
        return $dataToInsert;
    }

    /**
     * create User to Role CUstom
     */

    function createUserRoleCUstom($data, $cohort_id)
    {
        $dataToInsert = [
            "cohort_id" => $cohort_id,
            "school_code" => $data['school_code'],
            "staff_code" => $data['staff_code'],
            "custom_role" => $data['role'],
        ];
        // checking user already has customer role
        $userCustomeRoleExists = $this->userRoleModel->selectUserToRoleCustomShort($dataToInsert);
        if (sizeof($userCustomeRoleExists) > 0) {
            //! update user costomer role
            return true;
        }
        $this->userRoleModel->insertIntoUserToRoleCustom($dataToInsert);
        return $dataToInsert;
    }

    /**
     * handleTeacherStudyHierarchy
     */
    function handleTeacherStudyHierarchy($data)
    {
        if (isset($data['hierarchy_code']) && !empty($data['hierarchy_code'])) {
            $dataToInsert = [
                "staff_code" => $data['staff_code'],
                "study_hierarchy_id" => $data['hierarchy_code'],
            ];
            $teacherHeirarchyExists = $this->teacherStudyHierarchyModel->findTeacherStudyHierarchy($dataToInsert);
            if (sizeof($teacherHeirarchyExists) == 0) {
                $this->teacherStudyHierarchyModel->insertNewTeacherStudyHierarchy($dataToInsert);
            }
        }
        return $data;
    }

    /**
     * Handle bulk enrollment of teachers
     * @return Response
     */
    public function bulkEnroll()
    {
        try {
            // Get input data
            $data = json_decode(file_get_contents('php://input'), true);
            $created_by_user_id = AuthValidation::authorized()->id;
            $cohort_id = isset($data['cohort_id']) ? $data['cohort_id'] : null;

            // checking if cohort exists
            $cohortExists = $this->cohortsModel->getOneCohort($cohort_id);
            if (sizeof($cohortExists) == 0) {
                return Errors::notFoundError("Cohort id not found, please try again?");
            }

            // Validate data
            $this->bulkEnrollInputValidation($data["teachers"]);

            // temparary array
            $temp_success_array = array();

            // deplcated user
            $deplicated = array();
            // Process enrollment
            foreach ($data["teachers"] as $key => $teacherData) {
                // Create new user or update user
                $techerUploadStatus = "success";
                $processUserhandler = $this->createNewUserHandler($teacherData, $created_by_user_id);
                $processUser = $processUserhandler["user"];
                if (!$processUserhandler["deplicate"] && $processUser) {
                    // process user to role
                    $tempUserId = isset($processUser[0]["user_id"]) ? $processUser[0]["user_id"] : $processUser["user_id"];
                    $this->createUserAccessToRole($teacherData, $created_by_user_id, $tempUserId);

                    if (isset($teacherData["role"]) && strpos(strtolower($teacherData["role"]), "focal")) {
                        // insert user to user custom role
                        $this->createUserRoleCUstom($teacherData, $cohort_id);
                    } else if (isset($teacherData["role"]) && strtolower($teacherData["role"]) == "ssl") {
                        // insert user to user custom role
                        $this->createUserRoleCUstom($teacherData, $cohort_id);
                    } else {
                        // handle Teacher Study Hierarchy
                        $this->handleTeacherStudyHierarchy($teacherData);
                    }
                } else {
                    array_push($deplicated, $processUser);
                    $techerUploadStatus = "Failed";
                }

                $teacherData["status"] = $techerUploadStatus;
                array_push($temp_success_array, $teacherData);

                // save data to json file
                $encodeData = json_encode($temp_success_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                file_put_contents("./public/bulk_enroll_data.json", $encodeData);
            }

            // Prepare response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode(["deplicated" => $deplicated, "teachers" => $data["teachers"]]);
            return $response;
        } catch (InvalidDataException $e) {
            return Errors::badRequestError($e->getMessage());
        } catch (\Throwable $e) {
            // print_r($e);
            return Errors::databaseError($e->getMessage());
        }
    }

    /**
     * retrieve Json teachers
     * @return JSON
     */
    public function bulkEnrollRetrieveJson()
    {
        try {
            // authization
            $created_by_user_id = AuthValidation::authorized()->id;
            // fetching data from json file
            $json_data = file_get_contents("./public/bulk_enroll_data.json");

            // decode the $json_data
            $decoded_data = json_decode($json_data);

            // Prepare response
            $response['status_code_header'] = 'HTTP/1.1 200 Ok';
            $response['body'] = json_encode($decoded_data);
            return $response;
        } catch (\Throwable $e) {
            return Errors::databaseError($e->getMessage());
        }
    }

    /**
     * retrieve Json teachers
     * @return JSON
     */
    public function bulkEnrollDeleteJson()
    {
        try {
            // authization
            $created_by_user_id = AuthValidation::authorized()->id;

            // save data to json file
            $encodeData = json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents("./public/bulk_enroll_data.json", $encodeData);

            // Prepare response
            $response['status_code_header'] = 'HTTP/1.1 200 Ok';
            $response['body'] = json_encode([]);
            return $response;
        } catch (\Throwable $e) {
            return Errors::databaseError($e->getMessage());
        }
    }
}

$controller = new bulkEnrollController($this->db, $request_method, $params);
$controller->processRequest();
