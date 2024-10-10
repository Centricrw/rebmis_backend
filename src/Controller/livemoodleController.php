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
                if (sizeof($this->params) > 0 && $this->params['action'] == "get_enrollments") {
                    $response = $this->get_enrollments($this->params['id']);
                    break;
                }
                elseif (sizeof($this->params) > 0 && $this->params['action'] == "get_grades") {
                    $response = $this->get_grades($this->params['id']);
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

    private function get_grades($courseId)
    {

        try {
            $result = $this->livemoodleModel->get_grades($courseId);
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
