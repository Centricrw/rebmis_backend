<?php
namespace Src\Controller;

use Src\Models\ElearningModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class elearningController
{
    private $db;
    private $cohortsModel;
    private $request_method;
    private $params;
    private $cohortconditionModel;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->elearningModel = new ElearningModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // POST DATA
            case 'GET':
            if (sizeof($this->params) > 0) {
                if ($this->params['action'] == "connectCourse") {
                    $response = $this->connectCourse($this->params['course_id'], $this->params['cohort_id']);
                }
            }
            break;
            case 'POST':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "enrollToCourse") {
                        $response = $this->enrollToCourse();
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

    private function connectCourse($course_Id, $cohort_id)
    {
        
        error_reporting(E_ERROR | E_PARSE);
        try {
            $link = 'https://elearning.reb.rw/course/view.php?id='.$course_Id;
            $preresult = get_meta_tags($link)['keywords'];
            if($preresult){
                $this->elearningModel->connectCourse($cohort_id, $link);
                $result = trim($preresult, 'moodle, Course:');
            }
            else{
                $result = 'No course available';
            }
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function enrollToCourse()
    {
        
        error_reporting(E_ERROR | E_PARSE);
        //GET SUBMITED DATA
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $course_id = $data['course_id'];
        $username = $data['staff_code'];
        $cohort_name = $data['cohort_name'];
        $password = 'Education@123';
        try {
            // REGISTER A USER FROM SDMS IF NOT ALREADY REGISTERED
            $url1 = 'https://elearning.reb.rw/local/custom_service/misregistration.php?username='.$username.'&password='.$password.'';
            
            $curl = curl_init($url1);
            curl_setopt($curl, CURLOPT_URL, $url1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $resbjson1 = curl_exec($curl);
            $resp1 = (json_decode($resbjson1))->body;
            
            $result = new \stdClass;;
            $result->staff_code = $username;
            curl_close($curl);
            if($resbjson1){
                // ENROL A TEACHER ON THE COURSE
                $url2 = 'https://elearning.reb.rw/local/custom_service/assign_cpd_to_teacher.php?staff_code='.$username.'&course_id='.$course_id.'$cohort_name='.$cohort_name;
                $curl = curl_init($url2);
                curl_setopt($curl, CURLOPT_URL, $url2);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                
                $resbjson2 = curl_exec($curl);
                $resp2 = (json_decode($resbjson2))->body;

                curl_close($curl);
                if($resp2->status == 200){
                    // update the DB
                    $this->elearningModel->linkUserToCourse($staff_code, $course_Id);
                    $response['status_code_header'] = 'HTTP/1.1 201 Created';
                    $result->message = 'enrolled';
                    $result->reason = 'success';
                }else{
                    $response['status_code_header'] = 'HTTP/1.1 401 Created';
                    $result->message = 'not enrolled';
                    $result->reason = 'Error while enrolling on the course!';
                    //$result->url1 = $url1;
                    //$result->url2 = $url2;
                    //$result->debug1 =  $resbjson1;
                    //$result->debug2 =  $resbjson2;
                }
            }else{
                $response['status_code_header'] = 'HTTP/1.1 401 Created';
                $result->message = 'not_enrolled';
                $result->reason = 'Error while creating an account on elearning';
                //$result->url = $url;
            }
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }
}
$controller = new elearningController($this->db, $request_method, $params);
$controller->processRequest();