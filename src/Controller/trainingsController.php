<?php
namespace Src\Controller;

use Src\Models\TrainingsModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class trainingsController
{
    private $db;
    private $trainingsModel;
    private $request_method;
    private $params;
    private $userRoleModel;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->trainingsModel = new TrainingsModel($db);
        $this->userRoleModel = new UserRoleModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // GET DATA
            case 'GET':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "getall") {
                        $response = $this->getAllTranings();
                    } elseif ($this->params['action'] == "status") {
                        $response = $this->getTrainingsByStatusRebUser($this->params['status']);
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
                }
                break;

            case 'POST':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "create") {
                        $response = $this->addAtraining();
                    } elseif ($this->params['action'] == "comfirm") {
                        $response = $this->ComfirmTainingsByReb($this->params['status']);
                    } else {
                        $response = Errors::notFoundError("Route not found!");
                    }
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

    private function getAllTranings()
    {
        $result = $this->trainingsModel->getAllTranings();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getTrainingsByStatusRebUser($status)
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

        $user_id = AuthValidation::decodedData($jwt_data)->data->id;
        $user_role = $this->userRoleModel->findCurrentUserRole($user_id);

        if (sizeof($user_role) > 0 && strval($user_role[0]['role_id']) !== "4") {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
            $response['body'] = json_encode([
                'message' => "Not allowed to view this data, Please contact admistrator?",
            ]);
            return $response;
        }

        $result = $this->trainingsModel->getTraningsByStatus($status);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function addAtraining()
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

        $user_id = AuthValidation::decodedData($jwt_data)->data->id;

        $data = (array) json_decode(file_get_contents('php://input'), true);

        // Validate input if not empty
        if (!self::validateNewTraining($data)) {
            return Errors::unprocessableEntityResponse();
        }

        $result = $this->trainingsModel->addAtraining($data, $user_id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function ComfirmTainingsByReb($trainings_id)
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

        $user_id = AuthValidation::decodedData($jwt_data)->data->id;
        $data = (array) json_decode(file_get_contents('php://input'), true);

        // validate status Rejected, Ongoing
        if (!empty($data['status']) && $data["status"] !== "Rejected" && $data["status"] !== "Ongoing") {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
            $response['body'] = json_encode([
                'message' => $data['status'] . ", Invalid Status input, Please try again?",
            ]);
            return $response;
        }

        $user_role = $this->userRoleModel->findCurrentUserRole($user_id);

        if (sizeof($user_role) > 0 && strval($user_role[0]['role_id']) !== "4") {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
            $response['body'] = json_encode([
                'message' => "Not allowed to view this data, Please contact admistrator?",
            ]);
            return $response;
        }

        // checking if trainings id exists
        $trainingExist = $this->trainingsModel->findTrainingByID($trainings_id);

        if (sizeof($trainingExist) == 0) {
            $response['status_code_header'] = 'HTTP/1.1 400 bad request!';
            $response['body'] = json_encode([
                'message' => "Training not found, please try again?",
            ]);
            return $response;
        }
        // update or comfirm trainings status
        $result = $this->trainingsModel->comfirmTraining($trainings_id, $data["status"]);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            "message" => "Trainings status updated successfully!",
        ]);
        return $response;
    }

    private function validateNewTraining($input)
    {
        if (empty($input['trainingName'])) {
            return false;
        }
        if (empty($input['trainingDescription'])) {
            return false;
        }
        if (empty($input['trainingProviderId'])) {
            return false;
        }
        if (empty($input['startDate'])) {
            return false;
        }
        if (empty($input['endDate'])) {
            return false;
        }
        return true;
    }

}
$controller = new trainingsController($this->db, $request_method, $params);
$controller->processRequest();
