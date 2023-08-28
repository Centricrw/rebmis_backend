<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\CohortconditionModel;
  use Src\System\AuthValidation;

    class locationsController {
    private $db;
    private $cohortconditionModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->cohortconditionModel = new CohortconditionModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if(sizeof($this->params) > 0){
                    if($this->params['action'] == "getall"){
                        $response = $this->getallConditions($this->params['id']);
                    }
                }
                if(sizeof($this->params) > 0){
                    if($this->params['action'] == "gettrainees"){
                        $response = $this->getTrainees($this->params['id']);
                    }
                }
              break;

            case 'POST':
                if(sizeof($this->params) > 0){
                    if($this->params['action'] == "create"){
                        $response = $this->createCondition($this->params['id']);
                    }
                }
                if(sizeof($this->params) > 0){
                    if($this->params['action'] == "approveselected"){
                        $response = $this->approveselected($this->params['id']);
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
    
    private function getTrainees($conditionId){
        $result = $this->cohortconditionModel->getTrainees($conditionId);
  
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createCondition($cohortId){
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if(isset($all_headers['Authorization'])){
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if(empty($jwt_data->jwt)){
            return Errors::notAuthorized();
        }
        if(!AuthValidation::isValidJwt($jwt_data)){
            return Errors::notAuthorized();
        }

        $user_id = AuthValidation::decodedData($jwt_data)->data->id;

        $data = (array) json_decode(file_get_contents('php://input'), TRUE);

        // Validate input if not empty
        if(!self::validateNewCohortCondition($data)){
            return Errors::unprocessableEntityResponse();
        }
        $result = $this->cohortconditionModel->createCondition($data, $user_id, $cohortId);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function approveselected($cohortConditionId){
        $jwt_data = new \stdClass();

        $all_headers = getallheaders();
        if(isset($all_headers['Authorization'])){
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if(empty($jwt_data->jwt)){
            return Errors::notAuthorized();
        }
        if(!AuthValidation::isValidJwt($jwt_data)){
            return Errors::notAuthorized();
        }

        $user_id = AuthValidation::decodedData($jwt_data)->data->id;

        $data = (array) json_decode(file_get_contents('php://input'), TRUE);

        // Validate input if not empty
        $result = new \stdClass();;
        foreach($data['teachers'] as $aproved){ 
            $result->ids = $this->cohortconditionModel->approveselected($aproved['userId'], $user_id, $cohortConditionId);
        }
        $this->cohortconditionModel->cleanrejected($cohortConditionId);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getallConditions($cohortId){
        $result = $this->cohortconditionModel->getAllConditions($cohortId);
  
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function validateNewCohortCondition($input)
    {
        if (empty($input['conditions'])) {
            return false;
        }
        if (empty($input['location'])) {
            return false;
        }
        if (empty($input['limit'])) {
            return false;
        }
        if (empty($input['availabletrainees'])) {
            return false;
        }
        if (empty($input['trainingId'])) {
            return false;
        }
        return true;
    }
  }
    $controller = new locationsController($this->db, $request_method,$params);
    $controller->processRequest();