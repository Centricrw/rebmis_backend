<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\PostRequestReasonModel;

    class postRequestReasonsController {
    private $db;
    private $postRequestReasonModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->postRequestReasonModel = new PostRequestReasonModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                $response = $this->getReasons();
            break;
            default:
                $response = Errors::notFoundError('reason not found');
            break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    // Get all reasons
    function getReasons()
    {
        $result = $this->postRequestReasonModel->findAll();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


  }
    $controller = new postRequestReasonsController($this->db, $request_method,$params);
    $controller->processRequest();
