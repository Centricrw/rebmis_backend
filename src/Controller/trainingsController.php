<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\TrainingsModel;
  use Src\System\AuthValidation;

    class trainingsController {
    private $db;
    private $trainingsModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->trainingsModel = new TrainingsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // GET DATA
            case 'GET':
              if(sizeof($this->params) > 0){
                if($this->params['action'] == "getall"){
                  $response = $this->getAllTranings();
                }
              }
            break;

            case 'POST':
              if(sizeof($this->params) > 0){
                if($this->params['action'] == "create"){
                  $response = $this->addAtraining();
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

    private function getAllTranings(){
      $result = $this->trainingsModel->getAllTranings();

      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    private function addAtraining(){
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
      if(!self::validateNewTraining($data)){
        return Errors::unprocessableEntityResponse();
      }

      $result = $this->trainingsModel->addAtraining($data, $user_id);
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
    $controller = new trainingsController($this->db, $request_method,$params);
    $controller->processRequest();