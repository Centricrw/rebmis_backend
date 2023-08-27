<?php
namespace Src\Controller;

use Src\Models\RolesModel;
use Src\System\AuthValidation;
use Src\System\DatabaseConnector;
use Src\System\Errors;

class RolesController
{
    private $db;
    private $rolesModel;
    private $request_method;
    private $params;
    private $closeConnection;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->rolesModel = new RolesModel($db);
        $this->closeConnection = new DatabaseConnector();
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) == 1) {
                    $response = $this->getRoleById($this->params['id']);
                } else {
                    $response = $this->getAllRoles();
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

    // Get all roles
    function getAllRoles()
    {
        $DecodedData = AuthValidation::authorized();

        $result = $this->rolesModel->findAll();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Get a role by id
    function getRoleById($role_id)
    {
        $DecodedData = AuthValidation::authorized();

        $result = $this->rolesModel->findById($role_id);
        if (sizeof($result) > 0) {
            $result = $result[0];
        } else {
            $result = null;
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
}
$controller = new RolesController($this->db, $request_method, $params);
$controller->processRequest();
