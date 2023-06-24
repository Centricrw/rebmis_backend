<?php
    namespace Src\Controller;

    use Src\Models\SchoolLocationsModel;
    use Src\System\Errors;

    class DistrictsController {
    private $db;
    private $schoolLocationsModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->schoolLocationsModel = new SchoolLocationsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
              if(sizeof($this->params) == 1){
                $response = $this->getDistrictByCode($this->params['district_code']);
              }else{
                $response = $this->getAllDistricts();
              }
              break;
            default:
              $response = Errors::notFoundError("Route not found!");
            break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    // Get all roles
    function getAllDistricts()
    {
        $result = $this->schoolLocationsModel->districts();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Get a role by id 
    function getDistrictByCode($district_code)
    {
        $result = $this->schoolLocationsModel->findDistrictByCode($district_code);
        if(sizeof($result) > 0){
        $result = $result[0];
        }else{
            $result = null;
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
  }
    $controller = new DistrictsController($this->db, $request_method,$params);
    $controller->processRequest();
