<?php
namespace Src\Controller;

use Src\Models\SchoolLocationModal;
use Src\System\Errors;

class TeacherStudyHierarchyController
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

}

$controller = new TeacherStudyHierarchyController($this->db, $request_method, $params);
$controller->processRequest();
