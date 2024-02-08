<?php
namespace Src\Controller;

use Src\Models\ReportModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class reportController
{
    private $db;
    private $reportModel;
    private $userRoleModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->reportModel = new ReportModel($db);
        $this->userRoleModel = new UserRoleModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            case 'GET':
                if (sizeof($this->params) > 0 && $this->params['action'] == "getAll") {
                    $response = $this->getGeneralReport();
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "getPerSchool") {
                    $response = $this->getGeneralReport($this->params['schoolCode']);
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "getPerTraining") {
                    $response = $this->getGeneralReportPerTraining($this->params['id']);
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "getPerTrainee") {
                    $response = $this->getGeneralReportPerTrainee($this->params['id'], $this->params['cohort_id']);
                } else {
                    $response = Errors::notFoundError('Report route not found');
                }
                break;
            case "POST":
                if (sizeof($this->params) > 0 && $this->params['action'] == "mark") {
                    $response = $this->markTheTrainee();
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "headteacher") {
                    $response = $this->headTeacherTraineeMarkhandler();
                } else {
                    $response = Errors::notFoundError('Report route not found');
                }
                break;
            default:
                $response = Errors::notFoundError('Report route not found');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getGeneralReport()
    {
        $result = $this->reportModel->getGeneralReport();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getGeneralReportPerTraining($training_id)
    {
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            $user_role_details = sizeof($current_user_role) > 0 ? $current_user_role[0] : [];
            $school = isset($user_role_details['school_code']) ? $user_role_details['school_code'] : null;

            // if logged user is head teacher then return genaral report from school only
            if (isset($school) && !empty($school) && $user_role_details['role_id'] == "2") {
                $result = $this->reportModel->getGeneralReportPerTrainingForSchool($training_id, $school);
            } else {
                $result = $this->reportModel->getGeneralReportPerTraining($training_id);
            }

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function getGeneralReportPerTrainee($staff_code, $cohort_id)
    {
        try {
            $result = $this->reportModel->getGeneralReportPerTrainee($staff_code, $cohort_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function markTheTrainee()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);

        try {
            $result = $this->reportModel->markTheTrainee($inputData);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function headTeacherTraineeMarkhandler()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            $user_role_details = $current_user_role[0];

            // checking if is headteacher logged in
            if ($user_role_details['role_id'] != "2") {
                return Errors::badRequestError("Logged user is not head teacher, please try gain?");
            }

            $result = $this->reportModel->headTeacherTraineeMarking($inputData);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new reportController($this->db, $request_method, $params);
$controller->processRequest();
