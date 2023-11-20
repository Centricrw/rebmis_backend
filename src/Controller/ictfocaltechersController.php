<?php
namespace Src\Controller;

use Src\Models\IctfocalteachersModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class ictfocalteachersController
{
    private $db;
    private $ictfocalteachersModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->ictfocalteachersModel = new IctfocalteachersModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "getCandidates") {
                        $response = $this->getCandidates();
                    }
                }
            break;
            case 'POST':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "addFocalTeacher") {
                        $response = $this->addFocalTeacher();
                    }
                    elseif ($this->params['action'] == "getCandidates") {
                        $response = $this->getCandidates();
                    }
                    elseif ($this->params['action'] == "checkFocalTeacher") {
                        $response = $this->checkFocalTeacher();
                    }
                }
            break;
            default:
                $response = Errors::notFoundError("Route not found!");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    /**
     * Get all teachers on a school
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function getCandidates()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        try {
            $result = $this->ictfocalteachersModel->getCandidates($data);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    public function addFocalTeacher()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        try {
            $result = $this->ictfocalteachersModel->addFocalTeacher($data);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    public function checkFocalTeacher()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        try {
            $result = $this->ictfocalteachersModel->checkFocalTeacher($data);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new ictfocalteachersController($this->db, $request_method, $params);
$controller->processRequest();
