<?php
namespace Src\Controller;

use Src\Models\LivemoodleModel;
use Src\System\Errors;

class livemoodleController
{
    private $db;
    private $moodleDb;
    private $livemoodleModel;
    private $request_method;
    private $params;

    public function __construct($db, $moodleDb, $request_method, $params)
    {
        $this->db = $db;
        $this->moodleDb = $moodleDb;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->livemoodleModel = new LivemoodleModel($db, $moodleDb);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // GET DATA
            case "GET":
                if (sizeof($this->params) > 0 && $this->params['action'] == "get_enrollments" && $this->params['paid'] == "all") {
                    $response = $this->get_all_enrollments();
                    break;
                }elseif (sizeof($this->params) > 0 && $this->params['action'] == "get_enrollments") {
                    $response = $this->get_enrollments($this->params['paid']);
                    break;
                }
                elseif (sizeof($this->params) > 0 && $this->params['action'] == "get_pa_grades") {
                    $response = $this->get_pa_grades($this->params['paid']);
                    break;
                }
                elseif (sizeof($this->params) > 0 && $this->params['action'] == "get_course_grades") {
                    $response = $this->get_course_grades($this->params['previous_courseid'],$this->params['current_courseid']);
                    break;
                }
                break;
                
            case "POST":
                if (sizeof($this->params) > 0 && $this->params['action'] == "get_grades") {
                    $response = $this->get_grades();
                }else{
                    $response = Errors::notFoundError("nothing request provided");
                    break; 
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

    private function get_enrollments($courseId)
    {

        try {
            $result = $this->livemoodleModel->get_enrollments($courseId);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function get_all_enrollments()
    {
        $result = [];
        try {
            $all = new \stdClass; $all->id = "1"; $all->courseId = "0"; $all->enrolled = "All: 0";
            $test = new \stdClass; $test->id = "2"; $test->courseId = "889"; $test->enrolled = "PA Test: ".$this->livemoodleModel->get_enrollments("889");
            $bl1 = new \stdClass; $bl1->id = "3"; $bl1->courseId = "782"; $bl1->enrolled = "BL1: ".$this->livemoodleModel->get_enrollments("782");
            $bl2 = new \stdClass; $bl2->id = "4"; $bl2->courseId = "784"; $bl2->enrolled = "BL2: ".$this->livemoodleModel->get_enrollments("784");
            $bl3 = new \stdClass; $bl3->id = "5"; $bl3->courseId = "489"; $bl3->enrolled = "BL3: ".$this->livemoodleModel->get_enrollments("489");
            $il1 = new \stdClass; $il1->id = "6"; $il1->courseId = "788"; $il1->enrolled = "IL1: ".$this->livemoodleModel->get_enrollments("788");
            $il2 = new \stdClass; $il2->id = "7"; $il2->courseId = "789"; $il2->enrolled = "IL2: ".$this->livemoodleModel->get_enrollments("789");
            $il3 = new \stdClass; $il3->id = "8"; $il3->courseId = "790"; $il3->enrolled = "IL3: ".$this->livemoodleModel->get_enrollments("790");
            $certified = new \stdClass; $certified->id = "8"; $certified->courseId = "00"; $certified->enrolled = "Certified: 0";

            array_push($result,$test,$bl1,$bl2,$bl3,$il1,$il2,$il3,$certified);
            //$result = $this->livemoodleModel->get_enrollments($courseId);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function get_pa_grades($courseId)
    {

        try {
            $result = $this->livemoodleModel->get_pa_grades($courseId);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function get_course_grades($previous_courseid,$current_courseid)
    {
        $fruits = array($previous_courseid, $current_courseid);
        try {
            $result = $this->livemoodleModel->get_course_grades($previous_courseid,$current_courseid);
            // response
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new livemoodleController($this->db, $this->moodleDb, $request_method, $params);
$controller->processRequest();
