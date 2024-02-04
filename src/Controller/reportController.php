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
                elseif (sizeof($this->params) > 0 && $this->params['action'] == "getPerSchool") {
                  $response = $this->getGeneralReport($this->params['schoolCode']);
                }
                elseif (sizeof($this->params) > 0 && $this->params['action'] == "getPerTraining") {
                  $response = $this->getGeneralReportPerTraining($this->params['trainingId']);
                }
                break;
              case "POST":
                if (sizeof($this->params) > 0 && $this->params['action'] == "mark") {
                  $response = $this->markTheTrainee();
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

    private function getGeneralReport()
    {   
      $result = $this->reportModel->getGeneralReport();
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    private function getGeneralReportPerTraining($training_id)
    {   
      $result = $this->reportModel->getGeneralReportPerTraining($training_id);
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    
    private function markTheTrainee()
    {
      // getting input data
      $inputData = (array) json_decode(file_get_contents('php://input'), true);

      try {
          $result = $this->reportModel->markTheTrainee($inputData);
          // response
          $response['status_code_header'] = 'HTTP/1.1 201 Created';
          $response['body'] = json_encode($result);
          return $response;
      }
      catch (\Throwable $th) {
          return Errors::databaseError($th->getMessage());
      }
  }
}
$controller = new reportController($this->db, $request_method, $params);
$controller->processRequest();