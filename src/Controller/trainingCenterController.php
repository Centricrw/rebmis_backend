<?php
namespace Src\Controller;

use Src\Models\TrainingCenterModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class TrainingCenterController
{
    private $db;
    private $trainingCenterModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->trainingCenterModel = new TrainingCenterModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                $response = $this->getAllTrainingCenters();
                break;
            case "POST":
                $response = $this->createNewTrainingCenter();
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
            $this->trainingCenterModel->insertNewTrainingCenter($data, $user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
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

}
$controller = new TrainingCenterController($this->db, $request_method, $params);
$controller->processRequest();
