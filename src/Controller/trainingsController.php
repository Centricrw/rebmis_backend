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
            default:
              $response = Errors::notFoundError("no request provided");
            break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    function getAllTranings(){
      $result = $this->trainingsModel->getAllTranings();

      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

  }
    $controller = new trainingsController($this->db, $request_method,$params);
    $controller->processRequest();