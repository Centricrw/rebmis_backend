<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\ReportModel;
  use Src\System\AuthValidation;

    class reportController {
    private $db;
    private $reportModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->reportModel = new ReportModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            
              case 'GET':
                if (sizeof($this->params) > 0 && $this->params['action'] == "getAll") {
                  $response = $this->getGeneralReport();
                }
                break;
              case "POST":
                $response = 'testing post';
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

    private function getGeneralReport()
    {   
            $result = $this->reportModel->getGeneralReport();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
    }
}
$controller = new reportController($this->db, $request_method, $params);
$controller->processRequest();