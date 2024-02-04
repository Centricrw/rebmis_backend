<?php
namespace Src\Controller;

use Src\Models\TraineersModel;
use Src\Models\UserRoleModel;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class TraineersController
{
    private $db;
    private $traineersModel;
    private $request_method;
    private $userRoleModel;
    private $usersModel;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->traineersModel = new TraineersModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->usersModel = new UsersModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0) {
                    $response = $this->getTrainees($this->params['action']);
                } else {
                    $response = Errors::notFoundError("User trainees route not found, please try again?");
                }
                break;
            default:
                $response = Errors::notFoundError("User trainees route not found, please try again?");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getTrainees($cohortId)
    {
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $current_user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            $user_role_details = $current_user_role[0];
            $result = $this->traineersModel->getTrainees($cohortId, $user_role_details);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }
}

$controller = new TraineersController($this->db, $request_method, $params);
$controller->processRequest();
