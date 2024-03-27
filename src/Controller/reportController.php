<?php
namespace Src\Controller;

use DateTime;
use Src\Models\CohortconditionModel;
use Src\Models\ReportModel;
use Src\Models\TraineersModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class reportController
{
    private $db;
    private $reportModel;
    private $userRoleModel;
    private $cohortconditionModel;
    private $traineersModel;
    private $request_method;
    private $params;
    private $number;
    private $sign;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->reportModel = new ReportModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->cohortconditionModel = new CohortconditionModel($db);
        $this->traineersModel = new TraineersModel($db);
        $this->number = 0;
        $this->sign = "<";
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
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "status") {
                    $response = $this->getGeneralReportByStatus($this->params['id']);
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "addmissingreport") {
                    $response = $this->addMissingTrainingChapterHandler($this->params['id']);
                } else {
                    $response = Errors::notFoundError('Report route not found');
                }
                break;
            case "POST":
                if (sizeof($this->params) > 0 && $this->params['action'] == "mark") {
                    $response = $this->markTheTrainee();
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "headteacher") {
                    $response = $this->headTeacherTraineeMarkhandler();
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "updateelearningmarks") {
                    $response = $this->updateElearningMarks();
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "updateelearningselfassesment") {
                    $response = $this->updateelearningselfassesment();
                } elseif (sizeof($this->params) > 0 && $this->params['action'] == "count") {
                    $response = $this->countTraineeOnReport();
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

    private function updateElearningMarks()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);

        try {
            $result = $this->reportModel->updateElearningMarks($inputData);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function updateelearningselfassesment()
    {
        // getting input data
        $inputData = (array) json_decode(file_get_contents('php://input'), true);

        try {
            $result = $this->reportModel->updateelearningselfassesment($inputData);
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

    private function getGeneralReportByStatus($status = "Removed")
    {
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $result = $this->reportModel->selectGeneralReportByStatus($status);
            // response
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function addMissingTrainingChapterHandler($trainingId)
    {
        $logged_user_id = AuthValidation::authorized()->id;
        $currentYear = date("Y");
        $userNotFound = array();
        try {
            $trainees = $this->traineersModel->selectTraineeBYStatus("Approved");
            // get available chapters
            $modules = $this->cohortconditionModel->getAllReportsAssignedToTraining($trainingId);
            foreach ($trainees as $key => $data) {
                foreach ($modules as $key => $module) {
                    foreach ($module['details'] as $index => $chapter) {
                        // checking if user has chapter
                        $traineeHasChapter = $this->cohortconditionModel->traineeHasChapterHandler([
                            "user_id" => $data['userId'],
                            "cohortId" => $data['cohortId'],
                        ], $chapter['cop_report_details_id']);
                        if (count($traineeHasChapter) == 0) {
                            // get trainee information
                            $userDetails = $this->cohortconditionModel->getTraineeInfo($data['userId']);
                            if (count($userDetails) > 0) {
                                $userDetails = $userDetails[0];
                                // get age from dob
                                $age = null;
                                if (isset($userDetails["dob"])) {
                                    $dob = DateTime::createFromFormat("Y-m-d", $userDetails["dob"]);
                                    $age = $currentYear - $dob->format("Y");
                                }
                                // get school location
                                $schoolLocation = $this->cohortconditionModel->getTraineeSchoolLactionInfo($data['school_code']);
                                // insert trainee to general report
                                $traineeInfo = array(
                                    "traineeId" => $data["traineesId"],
                                    "userId" => $data["userId"],
                                    "traineeName" => $data["traineeName"],
                                    "traineePhone" => $data["traineePhone"],
                                    "staff_code" => $userDetails["staff_code"],
                                    "cohortId" => $data["cohortId"],
                                    "moduleId" => $module['cop_report_id'],
                                    "moduleName" => $module['cop_report_title'],
                                    "chapterId" => $chapter["cop_report_details_id"],
                                    "chapterName" => $chapter["cop_report_details_title"],
                                    "age" => $age,
                                    "gender" => $userDetails["sex"],
                                    "disability" => $userDetails["disability"],
                                    "district_code" => $schoolLocation["district_code"],
                                    "district_name" => $schoolLocation["district_name"],
                                    "sector_code" => $schoolLocation["sector_code"],
                                    "sector_name" => $schoolLocation["sector_name"],
                                    "school_code" => $schoolLocation["school_code"],
                                    "school_name" => $schoolLocation["school_name"],
                                    "trainingId" => $data["trainingId"],
                                );
                                $this->cohortconditionModel->insertTraineeToGeneralReport($traineeInfo);
                            } else {
                                array_push($userNotFound, $data);
                            }

                        }
                    }
                }
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                "message" => "Report updated successfuly",
                "count_user_not_found" => count($userNotFound),
                "user_not_found" => $userNotFound,
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function countTraineeOnReport()
    {
        $logged_user_id = AuthValidation::authorized()->id;
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);

        try {
            $this->number = $data["number"];
            $this->sign = $data["sign"];
            $greaterThanHandler = function ($value) {
                if ($this->sign == "<") {
                    return $this->number < $value["count"];
                } elseif ($this->sign == ">") {
                    return $this->number > $value["count"];
                } else {
                    return $this->number == $value["count"];
                }
            };
            $trainees = $this->reportModel->selectCountGeneralReportByTraining($data["training_id"]);
            $trainees = array_filter($trainees, $greaterThanHandler);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                "Count" => count($trainees),
                "trainees" => $trainees,
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new reportController($this->db, $request_method, $params);
$controller->processRequest();
