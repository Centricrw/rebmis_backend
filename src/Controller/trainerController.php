<?php
namespace Src\Controller;

use Src\Models\TrainerModel;
use Src\Models\TrainingsModel;
use Src\Models\UserRoleModel;
use Src\Models\UsersModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class TrainerController
{
    private $db;
    private $trainerModel;
    private $trainingsModel;
    private $request_method;
    private $params;
    private $userRoleModel;
    private $usersModel;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->trainerModel = new TrainerModel($db);
        $this->trainingsModel = new TrainingsModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->usersModel = new UsersModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (isset($this->params['action']) && $this->params['action'] == "training") {
                    $response = $this->getAllTrainersByTrainingId($this->params['training_id']);
                } else if (isset($this->params['action']) && $this->params['action'] == "user") {
                    $response = $this->getTrainerDatails($this->params['training_id']);
                } else {
                    $response = $this->getAllTrainers();
                }
                break;
            case "POST":
                $response = $this->insertNewTrainer();
                break;
            case "PUT":
                if (isset($this->params['action'])) {
                    $response = $this->updateTrainer($this->params['action']);
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

    public function insertNewTrainer()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        try {
            // checking if user already in the system
            $userExists = $this->usersModel->findOneUser($data['user_id']);
            if (sizeof($userExists) == 0) {
                return Errors::badRequestError("User not found, please register user first?");
            }

            // check if user already has training assigned to him/her
            $assignedToThisTraining = $this->trainerModel->checkIfTrainerAssignedtoThisTraining($data['user_id'], $data['training_id']);
            if (sizeof($assignedToThisTraining) > 0) {
                return Errors::badRequestError("Trainer already assigned to this training!, please contact administrator?");
            }

            // checking if user has active training

            // check if already has role
            $userHasActiveRole = $this->userRoleModel->findCurrentUserRole($data['user_id']);
            if (sizeof($userHasActiveRole) == 0) {
                // check role
                if (!isset($data['role_id'])) {
                    return Errors::badRequestError("Role id not provided?");
                }
                // assign trainer to role
                // Generate role_to_user_id
                $role_to_user_id = UuidGenerator::gUuid();
                $data['role_to_user_id'] = $role_to_user_id;
                $this->userRoleModel->insertIntoUserToRole($data, $logged_user_id);
            }

            // Generate trainer id
            $generated_trainer_id = UuidGenerator::gUuid();
            $data['trainers_id'] = $generated_trainer_id;
            $this->trainerModel->insertNewTrainer($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "trainers_id" => $data['trainers_id'],
                "user_id" => $data['user_id'],
                "training_id" => $data['training_id'],
                "message" => "Trainer created successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Updating trainer
     * @param {null}
     * @return {OBJECT} {results}
     */
    public function updateTrainer($trainers_id)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        try {
            // checking if user is arleady a trainer
            $userTrainerExists = $this->trainerModel->getTrainerById($trainers_id);
            if (sizeof($userTrainerExists) == 0) {
                return Errors::badRequestError("Trainer not found, please register trainer first?");
            }

            if ($userTrainerExists[0]['training_id'] != $data['training_id']) {
                // check if user already has training assigned to him/her
                $assignedToThisTraining = $this->trainerModel->checkIfTrainerAssignedtoThisTraining($data['user_id'], $data['training_id']);
                if (sizeof($assignedToThisTraining) > 0) {
                    return Errors::badRequestError("Trainer already assigned to this training!, please contact administrator?");
                }
            }

            // checking if user has active training

            $this->trainerModel->updateTrainer($data, $trainers_id, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "trainers_id" => $trainers_id,
                "user_id" => $userTrainerExists[0]['user_id'],
                "training_id" => $data['training_id'],
                "status" => $data['status'],
                "message" => "Training center created successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all training centers
     * @param {null}
     * @return {OBJECT} {results}
     */
    public function getAllTrainersByTrainingId($training_id)
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->trainerModel->getAllTrainersByTrainingId($training_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all training centers
     * @param {null}
     * @return {OBJECT} {results}
     */
    public function getTrainerDatails($user_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->trainerModel->getTrainerDatails($user_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all training centers
     * @param {null}
     * @return {OBJECT} {results}
     */
    public function getAllTrainers()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->trainerModel->getAllTrainers();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new TrainerController($this->db, $request_method, $params);
$controller->processRequest();
