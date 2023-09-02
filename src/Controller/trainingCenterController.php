<?php
namespace Src\Controller;

use Src\Models\TrainingCenterModel;
use Src\Models\TrainingsModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class TrainingCenterController
{
    private $db;
    private $trainingCenterModel;
    private $trainingsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->trainingCenterModel = new TrainingCenterModel($db);
        $this->trainingsModel = new TrainingsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (isset($this->params['action']) && $this->params['action'] == "training") {
                    $response = $this->getAllTrainingCentersAssignedToTraining($this->params['id']);
                } else {
                    $response = $this->getAllTrainingCenters();
                }
                break;
            case "POST":
                $response = $this->createNewTrainingCenter();
                break;
            case "PUT":
                if ($this->params['action'] == "assign") {
                    $response = $this->assignTrainingCenterToTraining();
                } else if ($this->params['action'] == "unassign") {
                    $response = $this->unassignTrainingCenterToTraining();
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

    /**
     * Create new training center
     * @param {OBJECT} {data}
     * @return {OBJECT} {results}
     */

    public function createNewTrainingCenter()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        // Generate user id
        $generated_user_id = UuidGenerator::gUuid();
        $data['training_centers_id'] = $generated_user_id;
        try {
            // checking if training center name exists
            // Remove whitespaces from both sides of a string
            $training_center_name = trim($data['training_centers_name']);
            $trainingCenterNameExists = $this->trainingCenterModel->getTrainingCenterByName(strtolower($training_center_name));
            if (sizeof($trainingCenterNameExists) > 0) {
                return Errors::badRequestError("Training center already exists, please try again?");
            }
            $this->trainingCenterModel->insertNewTrainingCenter($data, $user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "training_centers_id" => $data['training_centers_id'],
                "training_centers_name" => $data['training_centers_name'],
                "district_code" => $data['district_code'],
                "district_name" => $data['district_name'],
                "message" => "Training center created successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Un-assign training center to training
     * @param {OBJECT} {data}
     * @return {OBJECT} {results}
     */

    public function unassignTrainingCenterToTraining()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        try {

            // checking if training center already assigned to the same training
            $trainingExists = $this->trainingCenterModel->assignedTrainingCenterAndTraining($data['training_centers_id'], $data['training_id']);
            if (sizeof($trainingExists) == 0) {
                return Errors::badRequestError("This Training is not assigned to this training center, please try again?");
            }

            $this->trainingCenterModel->deleteAssignedTrainingCenterAndTraining($data['training_centers_id'], $data['training_id']);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                "message" => "Training center dismissed to training successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Assign training center to training
     * @param {OBJECT} {data}
     * @return {OBJECT} {results}
     */

    public function assignTrainingCenterToTraining()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        try {
            // checking if training center id exists
            $trainingCenteExists = $this->trainingCenterModel->getTrainingCenterById($data['training_centers_id']);
            if (sizeof($trainingCenteExists) == 0) {
                return Errors::notFoundError("Training center id not found, please try again?");
            }
            // checking if training id exists
            $trainingExists = $this->trainingsModel->getOneTraining($data['training_id']);
            if (sizeof($trainingExists) == 0) {
                return Errors::notFoundError("Training id not found, please try again?");
            }

            // checking if training center already assigned to the same training
            $trainingExists = $this->trainingCenterModel->assignedTrainingCenterAndTraining($data['training_centers_id'], $data['training_id']);
            if (sizeof($trainingExists) > 0) {
                return Errors::badRequestError("This Training already assigned to this training center, please try again?");
            }

            $this->trainingCenterModel->assignTrainingCanterToTraining($data['training_centers_id'], $data['training_id'], $user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Training center Assigned to training successfully!",
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
    public function getAllTrainingCenters()
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->trainingCenterModel->getAllTrainingCenter();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * all training centers assigned to training
     * @param {STRING} training_id
     * @return {OBJECT} {results}
     */
    public function getAllTrainingCentersAssignedToTraining($training_id)
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->trainingCenterModel->GetAllCentersAssignedToTraining($training_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new TrainingCenterController($this->db, $request_method, $params);
$controller->processRequest();
