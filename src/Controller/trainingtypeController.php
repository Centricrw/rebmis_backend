<?php
namespace Src\Controller;

use Src\Models\TrainingsModel;
use Src\Models\TrainingTypeModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class TrainingTypeController
{
    private $db;
    private $trainingTypeModel;
    private $trainingsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->trainingTypeModel = new TrainingTypeModel($db);
        $this->trainingsModel = new TrainingsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (isset($this->params['action']) && isset($this->params['training_type_id'])) {
                    $response = $this->getTrainingsAssignedToTrainingType($this->params['training_type_id']);
                } else if (isset($this->params['action']) && !isset($this->params['training_type_id'])) {
                    $response = $this->getOneTrainingType($this->params['action']);
                } else {
                    $response = $this->getAllTrainingType();
                }
                break;
            case "POST":
                $response = $this->createNewTrainingType();
                break;
            case "PUT":
                $response = $this->assignTrainingTypeToTraining();
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
     * Create new training type
     * @param {OBJECT} {data}
     * @return {OBJECT} {results}
     */

    public function createNewTrainingType()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // validating input data
        $validateData = self::validateAddingTrainingType($data);
        if (!$validateData['validated']) {
            return Errors::unprocessableEntityResponse($validateData['message']);
        }
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        // Generate training type id
        $generated_training_type_id = UuidGenerator::gUuid();
        $data['training_type_id'] = $generated_training_type_id;
        try {
            // Remove whitespaces from both sides of a string
            $training_type_name = trim($data['training_type_name']);
            // checking if training type name exists
            $trainingTypeNameExists = $this->trainingTypeModel->getTrainingTypeByName(strtolower($training_type_name));
            if (sizeof($trainingTypeNameExists) > 0) {
                return Errors::badRequestError("Training type already exists, please try again?");
            }
            $this->trainingTypeModel->insertNewTrainingType($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "training_type_id" => $data['training_type_id'],
                "training_type_name" => $data['training_type_name'],
                "description" => $data['description'],
                "message" => "Training center created successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Assign training type to training
     * @param {OBJECT} {data}
     * @return {OBJECT} {results}
     */

    public function assignTrainingTypeToTraining()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if training center id exists
            $trainingCenteExists = $this->trainingTypeModel->selectTrainingTypeById($data['training_type_id']);
            if (sizeof($trainingCenteExists) == 0) {
                return Errors::notFoundError("Training type id not found, please try again?");
            }
            // checking if training id exists
            $trainingExists = $this->trainingsModel->getOneTraining($data['training_id']);
            if (sizeof($trainingExists) == 0) {
                return Errors::notFoundError("Training id not found, please try again?");
            }

            $this->trainingTypeModel->assignTrainingTypeToTraining($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Training type Assigned to training successfully!",
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
    public function getAllTrainingType()
    {
        // geting authorized user id
        $user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->trainingTypeModel->selectAllTrainingsType();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get training type by id
     * @param {STRING} training_type_id
     * @return {OBJECT} {results}
     */
    public function getOneTrainingType($training_type_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->trainingTypeModel->selectTrainingTypeById($training_type_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * all trainings assigned to training type
     * @param {STRING} training_type_id
     * @return {OBJECT} {results}
     */
    public function getTrainingsAssignedToTrainingType($training_type_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->trainingTypeModel->selectTrainingsAssignedToTrainingType($training_type_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function validateAddingTrainingType($input)
    {
        if (empty($input['training_type_name'])) {
            return ["validated" => false, "message" => "training_type_name not provided!"];
        }
        if (empty($input['description'])) {
            return ["validated" => false, "message" => "description not provided!"];
        }
        return ["validated" => true, "message" => "OK"];
    }

}
$controller = new TrainingTypeController($this->db, $request_method, $params);
$controller->processRequest();
