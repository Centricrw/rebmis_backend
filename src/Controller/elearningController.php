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
}
$controller = new elearningController($this->db, $request_method, $params);
$controller->processRequest();
