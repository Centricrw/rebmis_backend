<?php
namespace Src\Controller;

use Src\Models\CohortconditionModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class locationsController
{
    private $db;
    private $cohortconditionModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->cohortconditionModel = new CohortconditionModel($db);
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
        $result = $this->cohortconditionModel->getTrainees($conditionId);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
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
            foreach ($data['teachers'] as $aproved) {
                //check if teacher assigned to this training
                $traineerExist = $this->cohortconditionModel->checkIfTraineerAvailable($aproved['trainingId'], $aproved['user_id']);
                if (sizeof($traineerExist) == 0) {
                    // Generate traineer id
                    $generated_traineer_id = UuidGenerator::gUuid();
                    $aproved['traineesId'] = $generated_traineer_id;
                    $this->cohortconditionModel->InsertApprovedSelectedTraineers($aproved, $logged_user_id);
                }
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

    private function getTeacherByCondition()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        // getting condition
        try {
            $result = $this->cohortconditionModel->getTeacherByConditions($data);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
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
}
$controller = new locationsController($this->db, $request_method, $params);
$controller->processRequest();
