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
    private $elearningModel;
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
        
        $userMis = ($this->usersModel->findUserByStaffcode($staff_code))[0];
        $firstname = $userMis['first_name'];
        $lastname = $userMis['last_name'];
        $username = $userMis['staff_code'];
        $email = $userMis['email'];
        $password = 'Education@123';
        try {
            $url = 'https://elearning.reb.rw/local/custom_service/userregister.php?firstname='.$firstname.'&lastname='.$lastname.'&username='.$username.'&email='.$email.'&password='.$password.'';
            
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = 'no';
            $resp = curl_exec($curl);
            curl_close($curl);
            if($resp){
                $url2 = 'https://elearning.reb.rw/local/custom_service/assign_cpd_to_teacher.php?staff_code='.$username.'&course_id=713&cohort_name=FHI2024';
                $curl = curl_init($url2);
                curl_setopt($curl, CURLOPT_URL, $url2);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $resp2 = curl_exec($curl);
                curl_close($curl);
                if($resp2){
                    // update the DB
                    $this->elearningModel->linkUserToCourse($staff_code, $course_Id);
                        $obj1 = new \stdClass;
                        $obj1->staff_code = $username;
                        $obj1->status = 'enrolled';
                        $result = $obj1;
                }
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
