<?php
namespace Src\Controller;

use Src\Models\CohortconditionModel;
use Src\Models\CohortsModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class cohortsController
{
    private $db;
    private $cohortsModel;
    private $request_method;
    private $params;
    private $cohortconditionModel;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->cohortsModel = new CohortsModel($db);
        $this->cohortconditionModel = new CohortconditionModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // GET DATA
            case 'GET':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "getall") {
                        $response = $this->getAllCohorts($this->params['id']);
                    }
                }
                break;

            case 'POST':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "create") {
                        $response = $this->addACohort($this->params['id']);
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

    private function getAllCohorts($trainingId)
    {
        $results = $this->cohortsModel->getAllCohorts($trainingId);
        $newCohortWithCondition = [];
        foreach ($results as $result) {
            $cohortConditions = $this->cohortconditionModel->getAllConditions($result['cohortId']);
            $result['cohorts_condition'] = $cohortConditions;
            array_push($newCohortWithCondition, $result);
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($newCohortWithCondition);
        return $response;
    }

    private function addACohort($trainingId)
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
        if (!self::validateNewCohort($data)) {
            return Errors::unprocessableEntityResponse();
        }

        $result = $this->cohortsModel->addACohort($data, $user_id, $trainingId);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
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
        if (empty($input['offerMode'])) {
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

    private function validateNewCohort($input)
    {
        if (empty($input['cohortName'])) {
            return false;
        }
        if (empty($input['cohortStart'])) {
            return false;
        }
        if (empty($input['cohortEnd'])) {
            return false;
        }
        return true;
    }

}
$controller = new cohortsController($this->db, $request_method, $params);
$controller->processRequest();
