<?php
namespace Src\Controller;

use Src\Models\SystemFunctionModal;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class SystemFunctionController
{
    private $db;
    private $usersModel;
    private $request_method;
    private $params;
    private $systemFuctionModal;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->usersModel = new UsersModel($db);
        $this->systemFuctionModal = new SystemFunctionModal($db);
    }

    public function processRequest()
    {
        switch ($this->request_method) {
            case 'POST':
                if ($this->params['action'] == "create") {
                    $response = $this->insertNewFunction();
                } else if ($this->params['action'] == "update") {
                    $response = Errors::notFoundError("Route in Development!");
                } else if ($this->params['action'] == "delete") {
                    $response = Errors::notFoundError("Route in Development!");
                } else {
                    $response = Errors::notFoundError("Route not found!");
                }
                break;
            case 'GET':
                $response = $this->getAllFunctions();
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

    public function getAllFunctions()
    {
        $result = $this->systemFuctionModal->findAllFucntionsDbHandler();
        $response['status_code_header'] = 'HTTP/1.1 200 Ok';
        $response['body'] = json_encode($result);
        return $response;
    }

    public function insertNewFunction()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;

        // checking if validation of system_name is  avilable
        if (empty($data['function_name'])) {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request';
            $response['body'] = json_encode([
                'message' => "function_name is not provided!",
            ]);
            return $response;
        }

        // checking if function name exist
        $checkNameExist = $this->systemFuctionModal->findAllFucntionsByNameDbHandler($data['function_name']);
        if (!empty($checkNameExist)) {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request';
            $response['body'] = json_encode([
                'message' => "function_name is Already Exists!",
            ]);
            return $response;
        }
        // inserting new function name handler
        $this->systemFuctionModal->insertNewFunctionDbHandler($data, $user_id);
        $result = $this->systemFuctionModal->findAllFucntionsByNameDbHandler($data['function_name']);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode([
            'message' => "System Fuction Inserted Successfuly!",
            "results" => $result[0],
        ]);
        return $response;
    }

}

$controller = new SystemFunctionController($this->db, $request_method, $params);
$controller->processRequest();
