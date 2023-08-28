<?php
  namespace Src\Controller;
  use Src\System\Errors;
  use Src\Models\LocationsModel;
  use Src\System\AuthValidation;

    class locationsController {
    private $db;
    private $locationsModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
      $this->db = $db;
      $this->request_method = $request_method;
      $this->params = $params;
      $this->locationsModel = new LocationsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {

            // GET DATA
            case 'GET':
              if(sizeof($this->params) > 0){
                
                if($this->params['action'] == "getprovince"){
                    $response = $this->getProvince($this->params['id']);
                  }
                if($this->params['action'] == "getdistricts"){
                  $response = $this->getDistricts($this->params['id']);
                }
                if($this->params['action'] == "getsectors"){
                    $response = $this->getSectors($this->params['id']);
                }
                if($this->params['action'] == "getcells"){
                    $response = $this->getCells($this->params['id']);
                }
                if($this->params['action'] == "getvillages"){
                    $response = $this->getVillages($this->params['id']);
                }
                if($this->params['action'] == "getperschool"){
                    $response = $this->getSchools($this->params['id']);
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
    
    private function getProvince($provinceId){
        $result = $this->locationsModel->getprovince($provinceId);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getDistricts($provinceId){
        $result = $this->locationsModel->getdistricts($provinceId);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getSectors($districtId){
      $result = $this->locationsModel->getsectors($districtId);

      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    private function getCells($sectorId){
        $result = $this->locationsModel->getcells($sectorId);
  
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getVillages($cellId){
        $result = $this->locationsModel->getvillages($cellId);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getSchools($villageId){
        $result = $this->locationsModel->getperschool($villageId);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
  }
    $controller = new locationsController($this->db, $request_method,$params);
    $controller->processRequest();