<?php
namespace Src\Controller;

use Src\Models\BulkEnrollModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class bulkEnrollController
{
    private $db;
    private $bulkEnrollModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->bulkEnrollModel = new BulkEnrollModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'POST':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "bulkenroll") {
                        $response = $this->bulkEnroll();
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

    public function bulkEnroll()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        try {
            $result = $this->bulkEnrollModel->bulkEnroll($data);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }
}
$controller = new bulkEnrollController($this->db, $request_method, $params);
$controller->processRequest();
