<?php
namespace Src\Controller;

use Src\Models\ElearningModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\Models\UsersModel;

class elearningEnrollmentController
{
    private $db;
    private $cohortsModel;
    private $usersModel;
    private $request_method;
    private $params;
    private $cohortconditionModel;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->elearningModel = new ElearningModel($db);
        $this->usersModel = new UsersModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // POST DATA
            case 'GET':
                if (sizeof($this->params) > 0) {
                    $response = $this->enrolToCourse($this->params['courseCode'], $this->params['staff_code']);
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

    private function enrolToCourse($course_Id, $staff_code)
    {
        
        error_reporting(E_ERROR | E_PARSE);
        // GET USER DATA
        
        $userMis = $this->usersModel->findUserByStaffcode($staff_code);
        $firstname = $userMis['first_name'];
        $lastname = $userMis['last_name'];
        $username = $userMis['username'];
        $email = $userMis['email'];
        $password = 'Education@123';
        try {
            $link = 'https://elearning.reb.rw/sandbox/local/custom_service/userregister.php?firstname='.$firstname.'&lastname='.$lastname.'&username='.$username.'&email='.$email.'&password='.$password.'';
            $preresult = get_meta_tags($link)['keywords'];
            if($preresult){
                //$this->elearningModel->connectCourse($cohort_id, $link);
                $result = $userMis;
            }
            else{
                $result = $userMis;
            }
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }
}
$controller = new elearningEnrollmentController($this->db, $request_method, $params);
$controller->processRequest();
