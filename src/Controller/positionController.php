<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\PositionModel;

    class positionController {
    private $db;
    private $positionModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->positionModel = new PositionModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
              if(sizeof($this->params) == 1){
                $response = $this->getPosition($this->params['id']);
              }else{
                $response = $this->getPositions();
              }
              break;
            case 'POST':
              $response = $this->addPosition();
              break;
            case 'DELETE':
              if(sizeof($this->params) == 1){
                $response = $this->deletePosition($this->params['id']);
              }
            default:
              $response = Errors::notFoundError('plan not found');
            break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    // Get all positions
    function getPositions()
    {
        $result = $this->positionModel->findAll();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Get a position by id 
    function getPosition($params)
    {
        $result = $this->positionModel->findOne($params);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Add a position
    function addPosition(){
      $input = (array) json_decode(file_get_contents('php://input'), TRUE);
      // Validate input if not empty
      if(!self::validatePositionInfo($input)){
        return Errors::unprocessableEntityResponse();
      }
      
      $result = $this->positionModel->insert($input);
      //$userAuthData = $this->authModel->findOne($input['username']);
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($input);
      return $response;
    }

    // Delete a position
    function deletePosition($positionId){
      $plan = $this->positionModel->findOne($positionId);
      if (sizeof($plan) == 0) {
          return Errors::notFoundError("Plan not found!");
      }

      $this->positionModel->delete($positionId,1);

      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode(["message"=>"Account Suspended!"]);
      return $response;
    }

    private function validatePositionInfo($input)
    {
        if (empty($input['position_code'])) {
            return false;
        }
        if (empty($input['position_name'])) {
            return false;
        }
        if (empty($input['createdB_by'])) {
          return false;
        }
        return true;
    }
  }
    $controller = new positionController($this->db, $request_method,$params);
    $controller->processRequest();
