<?php
namespace Src\Controller;

use Src\Models\SchoolLocationModal;
use Src\System\AuthValidation;
use Src\System\Errors;

class schoolLocationController
{
    private $db;
    private $schoolLocationModal;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->schoolLocationModal = new SchoolLocationModal($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'POST':
                if ($this->params['action'] == "location") {
                    $response = $this->getSchoolLocation();
                } else {
                    $response = Errors::notFoundError("Route not found!");
                }
                break;
            case 'GET':
                if (sizeof($this->params) > 0) {
                    $response = $this->getSchoolLocation();
                }
                break;
            default:
                $response = Errors::notFoundError('Route not found');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    public function getSchoolLocation()
    {
        try {
            // getting input data
            $data = (array) json_decode(file_get_contents('php://input'), true);
            // geting authorized user id
            $user_id = AuthValidation::authorized()->id;

            // getting school location by passing location
            $result = $this->schoolLocationModal->findSchoolLocation($data);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}

$controller = new schoolLocationController($this->db, $request_method, $params);
$controller->processRequest();
