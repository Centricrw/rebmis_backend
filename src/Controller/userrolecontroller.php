<?php
namespace Src\Controller;

use Src\Models\RolesModel;
use Src\Models\SystemFunctionModal;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class UserRolesController
{
    private $db;
    private $rolesModel;
    private $userRoleModel;
    private $request_method;
    private $params;
    private $systemFuctionModal;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->rolesModel = new RolesModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->systemFuctionModal = new SystemFunctionModal($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) == 1) {
                    $response = $this->getUserRoleById($this->params['action']);
                } else {
                    $response = $this->getAllUserRoles();
                }
                break;
            case "POST":
                if ($this->params['action'] == "assign") {
                    $response = $this->assignAccessToRole();
                } else if ($this->params['action'] == "unassign") {
                    $response = $this->unAssignAccessToRole();
                } else {
                    $response = Errors::notFoundError("Route not found!");
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
    // Get all roles
    function disactiveUser()
    {
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if (isset($all_headers['Authorization'])) {
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if (empty($jwt_data->jwt)) {
            return Errors::notAuthorized();
        }
        if (!AuthValidation::isValidJwt($jwt_data)) {
            return Errors::notAuthorized();
        }
        $result = $this->rolesModel->findAll();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    // Get all roles
    function getAllUserRoles()
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;

        $result = $this->rolesModel->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Get a role by id
    function getUserRoleById($role_id)
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;

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

    public function assignAccessToRole()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;

        // checking if validation of access is  avilable
        if (empty($data['access'])) {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request';
            $response['body'] = json_encode([
                'message' => "access is not provided!",
            ]);
            return $response;
        }

        // checking if role exist
        $checkNameExist = $this->systemFuctionModal->findRoleByIdDbHandler($data['role_id']);
        if (empty($checkNameExist)) {
            return $response = Errors::notFoundError("Role Id Not Found!");
        }
        $currentAccess = $checkNameExist[0]['access'];

        // chceking if access exist
        $accessArray = explode(",", $data['access']);
        foreach ($accessArray as $item) {
            $checkAccessExist = $this->systemFuctionModal->findAllFucntionsByNameDbHandler($item);
            if (empty($checkAccessExist)) {
                return $response = Errors::notFoundError("$item Access Not Found!");
            }
        }

        // exlact data from check exist
        $newAccess = $currentAccess . "," . $data['access'];
        // assign new access to role
        $this->systemFuctionModal->assignAccessToRoleDbHandler($newAccess, $data['role_id']);
        $results = $this->systemFuctionModal->findRoleByIdDbHandler($data['role_id']);
        $response['status_code_header'] = 'HTTP/1.1 200 Ok';
        $response['body'] = json_encode([
            'message' => "Access Assigned Successfuly!",
            "results" => $results,
        ]);
        return $response;

    }

    public function unAssignAccessToRole()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;

        // checking if validation of access is  avilable
        if (empty($data['access'])) {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request';
            $response['body'] = json_encode([
                'message' => "access is not provided!",
            ]);
            return $response;
        }

        // checking if role exist
        $checkNameExist = $this->systemFuctionModal->findRoleByIdDbHandler($data['role_id']);
        if (empty($checkNameExist)) {
            return $response = Errors::notFoundError("Role Id Not Found!");
        }
        $currentAccess = $checkNameExist[0]['access'];

        // chceking if access exist
        $accessArray = explode(",", $currentAccess);
        $accessToRemoveArray = explode(",", $data['access']);
        $newAccess = "";
        foreach ($accessArray as $item) {
            if (!in_array($item, $accessToRemoveArray)) {
                $newAccess .= $item . ",";
            }
        }

        // assign new access to role
        $newarrayaccess = rtrim($newAccess, ", ");
        $this->systemFuctionModal->assignAccessToRoleDbHandler($newarrayaccess, $data['role_id']);
        $results = $this->systemFuctionModal->findRoleByIdDbHandler($data['role_id']);
        $response['status_code_header'] = 'HTTP/1.1 200 Ok';
        $response['body'] = json_encode([
            'message' => "Access Assigned Successfuly!",
            "results" => $results,
        ]);
        return $response;

    }
}
$controller = new UserRolesController($this->db, $request_method, $params);
$controller->processRequest();
