<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\QualificationModel;

    class qualificationController {
    private $db;
    private $qualificationModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->qualificationModel = new QualificationModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                $response = $this->getQualifications();
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

    // Get all Qualifications
    function getQualifications()
    {
        $result = $this->qualificationModel->findAll();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
  }
    $controller = new qualificationController($this->db, $request_method,$params);
    $controller->processRequest();
