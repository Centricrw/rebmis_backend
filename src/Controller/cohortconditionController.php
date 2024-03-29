<?php
namespace Src\Controller;

use Src\Models\CohortconditionModel;
use Src\Models\UserRoleModel;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\InvalidDataException;
use Src\System\UuidGenerator;

class locationsController
{
    private $db;
    private $cohortconditionModel;
    private $request_method;
    private $userRoleModel;
    private $usersModel;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->cohortconditionModel = new CohortconditionModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->usersModel = new UsersModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "getall") {
                        $response = $this->getallConditions($this->params['id']);
                    } else if ($this->params['action'] == "gettrainees") {
                        $response = $this->getTrainees($this->params['id']);
                    } else if ($this->params['action'] == "studyhierarchy") {
                        $response = $this->GetStudyHierarchy();
                    } else if ($this->params['action'] == "generalreport") {
                        $response = $this->getAvailableChapters($this->params['id']);
                    } else {
                        $response = Errors::notFoundError("No route found!");
                    }
                }
                break;

            case 'POST':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "create") {
                        $response = $this->createCondition();
                    } else if ($this->params['action'] == "condition") {
                        $response = $this->getTeacherByCondition();
                    } else if ($this->params['action'] == "school") {
                        $response = $this->getSchoolsByLocation();
                    } else if ($this->params['action'] == "approveselected") {
                        $response = $this->approveselected($this->params['id']);
                    } else {
                        $response = Errors::notFoundError("No route found!");
                    }
                }
                break;
            default:
                $response = Errors::notFoundError("no request provided");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getTrainees($conditionId)
    {
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            if (sizeof($current_user_role) > 0) {
                $userRole = $current_user_role[0]['role_id'];
                $userSchoolCode = $current_user_role[0]['school_code'];
                $userSectorCode = $current_user_role[0]['sector_code'];
                $userDistrictCode = $current_user_role[0]['district_code'];
            }
            $result = $this->cohortconditionModel->getTrainees($conditionId, $userDistrictCode);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function getSchoolsByLocation()
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        $location = (array) json_decode(file_get_contents('php://input'), true);
        try {
            $result = $this->cohortconditionModel->getSchoolsByLocation($location);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function createCondition()
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;

        $data = (array) json_decode(file_get_contents('php://input'), true);

        // Validate input if not empty
        $validationInputData = self::validateNewCohortCondition($data);
        if (!$validationInputData['validated']) {
            return Errors::unprocessableEntityResponse($validationInputData['message']);
        }
        try {
            // Generate cohort condition id
            $generated_condition_id = UuidGenerator::gUuid();
            $data['cohortconditionId'] = $generated_condition_id;
            $this->cohortconditionModel->createCondition($data, $user_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $data["message"] = "Cohort condition created successfully!";
            $response['body'] = json_encode($data);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function approveselected($cohortConditionId)
    {
        $logged_user_id = AuthValidation::authorized()->id;

        $data = (array) json_decode(file_get_contents('php://input'), true);
        try {
            // get cohort condition details
            $conditionDetails = $this->cohortconditionModel->selectCohortConditionById($data['teachers'][0]['cohortconditionId']);

            foreach ($data['teachers'] as $aproved) {
                //count avaible traineers
                $availableTraineers = $this->cohortconditionModel->countTraineersOnCondition($aproved);
                // if (sizeof($availableTraineers) == (int) $conditionDetails[0]['capacity']) {
                //     return Errors::badRequestError("Needed Traineers completed!");
                // } else {
                //check if teacher assigned to this training
                $traineerExist = $this->cohortconditionModel->checkIfTraineerAvailable($aproved['trainingId'], $aproved['user_id']);
                if (sizeof($traineerExist) == 0) {
                    // Generate traineer id
                    $generated_traineer_id = UuidGenerator::gUuid();
                    $aproved['traineesId'] = $generated_traineer_id;
                    $this->cohortconditionModel->InsertApprovedSelectedTraineers($aproved, $logged_user_id);
                }
                // }
            }

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                'message' => "Traineers assigned succefully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function getAvailableChapters($traineensId)
    {
        $logged_user_id = AuthValidation::authorized()->id;

        try {
            $chapters = $this->cohortconditionModel->getAllReportsAssignedToTraining($traineensId);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($chapters);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function getallConditions($cohortId)
    {
        try {
            //code...
            $result = $this->cohortconditionModel->getAllConditions($cohortId);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function GetStudyHierarchy()
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        try {
            $result = $this->cohortconditionModel->GetStudyHierarchy();

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    function userIdsHandler($users)
    {
        return $users['userId'];
    }

    /**
     * checking if user is allowed
     * @param boolean $include_trained
     * @param boolean $isTrained
     * @return boolean
     */
    function trainedIsAllowed($include_trained, $isTrained)
    {
        return $include_trained ? true : !$isTrained;
    }

    function FilterTraineersBYtrainingType($data)
    {
        try {
            // traineens tamporaly array
            $traineensArray = array();
            // getting traineers that allready trained to that training type
            // $traineers = $this->cohortconditionModel->selectTraineersForBYtrainingType($data['training_type_id']);
            $traineers = $this->cohortconditionModel->selectTraineersForBYtrainingType($data['cohortId']);
            $traineensId = sizeof($traineers) > 0 ? array_map(array($this, 'userIdsHandler'), $traineers) : [];

            // limit
            $limit = (int) $data['capacity'];
            // checking if limit is number
            if (!is_numeric($limit)) {
                throw new InvalidDataException("capacity must be number, please try again");
            }

            $finish = 0;
            $offSet = 0;
            $numberLimit = $limit;
            while ($finish < 1) {
                $result = $this->cohortconditionModel->getTeacherByConditionsLimit($data, $numberLimit, $offSet);
                if (sizeof($result) == $numberLimit) {
                    foreach ($result as $key => $value) {
                        $trained = in_array($value['user_id'], $traineensId);
                        if ($this->trainedIsAllowed($data['include_trained'], $trained)) {
                            $value['trained'] = $trained;
                            array_push($traineensArray, $value);
                        }
                    }
                    if (sizeof($traineensArray) == $limit) {
                        $finish = 1;
                    } else {
                        $offSet = $offSet + $numberLimit;
                        $numberLimit = $limit - sizeof($traineensArray);
                    }
                } else {
                    foreach ($result as $key => $value) {
                        $trained = in_array($value['user_id'], $traineensId);
                        if ($this->trainedIsAllowed($data['include_trained'], $trained)) {
                            $value['trained'] = $trained;
                            array_push($traineensArray, $value);
                        }
                    }
                    $finish = 1;
                }
            }
            return $traineensArray;
        } catch (\Throwable $th) {
            throw new InvalidDataException($th->getMessage());
        }
    }

    private function getTeacherByCondition()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $data['include_trained'] = false;
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        // Validate input if not empty
        $validationInputData = self::validateGetTeachersByCondition($data);
        if (!$validationInputData['validated']) {
            return Errors::unprocessableEntityResponse($validationInputData['message']);
        }

        try {
            // getting condition
            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            $result = $this->FilterTraineersBYtrainingType($data);

            // if (sizeof($current_user_role) > 0 && $current_user_role[0]['role_id'] == 2) {
            //     $schoolCode = $current_user_role[0]['school_code'];
            //     $newResults = [];
            //     foreach ($result as $key => $value) {
            //         if ($value['school_code'] == $schoolCode) {
            //             array_push($newResults, $value);
            //         }
            //     }
            //     $result = $newResults;
            // }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (InvalidDataException $e) {
            return Errors::unprocessableEntityResponse($e->getMessage());
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function validateNewCohortCondition($input)
    {
        if (empty($input['provincecode'])) {
            return ["validated" => false, "message" => "provincecode not provided!"];
        }
        if (empty($input['district_code'])) {
            return ["validated" => false, "message" => "district_code not provided!"];
        }
        if (empty($input['capacity'])) {
            return ["validated" => false, "message" => "capacity not provided!"];
        }
        if (empty($input['cohortId'])) {
            return ["validated" => false, "message" => "cohortId not provided!"];
        }
        if (empty($input['approval_role_id'])) {
            return ["validated" => false, "message" => "approval_role_id not provided!"];
        }
        return ["validated" => true, "message" => "OK"];
    }

    private function validateGetTeachersByCondition($input)
    {
        if (empty($input['cohortId'])) {
            return ["validated" => false, "message" => "cohortId not provided!"];
        }
        if (empty($input['capacity'])) {
            return ["validated" => false, "message" => "capacity not provided!"];
        }
        // if (empty($input['training_type_id'])) {
        //     return ["validated" => false, "message" => "training_type_id not provided!"];
        // }
        return ["validated" => true, "message" => "OK"];
    }
}
$controller = new locationsController($this->db, $request_method, $params);
$controller->processRequest();
