<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\ProfileModel;
  use Src\System\AuthValidation;

    class profileController {
    private $db;
    private $profileModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->profileModel = new ProfileModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            
              case 'GET':
                if(sizeof($this->params) > 0){
                  $response = $this->getProfile($this->params['usertype'], $this->params['id']);
                }
                break;
            default:
              $response = Errors::notFoundError('plan not found');
            break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    // Get all calendars
    function getProfile($usertype, $user_id)
    {
        // $result = ["id"=> $params["id"]]; 
        $result = $this->profileModel->getProfile($usertype, $user_id);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
  }
    $controller = new profileController($this->db, $request_method,$params);
    $controller->processRequest();
