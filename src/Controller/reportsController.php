<?php
namespace Src\Controller;

use Src\Models\UsersModel;
use Src\Modles\ReportsModel;
use Src\System\AuthValidation;
use Src\System\DatabaseConnector;
use Src\System\Encrypt;
use Src\System\Errors;

class reportsController
{
    private $db;
    private $usersModel;
    private $reportsModel;
    private $request_method;
    private $params;
    private $closeConnection;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
    }

    public function processRequest()
    {
        switch ($this->request_method) {
            case 'POST':
                if ($this->params['action'] == "general") {
                    $response = $this->getGeneralReports();
                } 
                break;
            default:
                $response = Errors::notFoundError("Route not found!");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            $this->closeConnection->closeConnection();
            echo $response['body'];
        }
    }

    public function getGeneralReports()
    {
        
        $results = $this->reportsModel->getGeneralReports();
        $response['status_code_header'] = 'HTTP/1.1 200 Ok';
        $response['body'] = json_encode($results);
        return $response;
    }
}
$controller = new reportsController($this->db, $request_method, $params);
$controller->processRequest();
