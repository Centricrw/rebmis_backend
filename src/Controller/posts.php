<?php
namespace Src\Controller;

use Src\Models\PostsModel;
use Src\Models\RebDistributionsModel;
use Src\System\Errors;
use Src\System\AuthValidation;
use Src\Validations\LimitsValidation;



  class PostsController {
    private $db;
    private $postsModel;
    private $rebDistributionsModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->postsModel = new PostsModel($db);
        $this->rebDistributionsModel = new RebDistributionsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET': 
              if(sizeof($this->params) > 0){
                if($this->params['action'] == "peracademic"){
                  $response = $this->getDistrictDistributionByAcademic($this->params['district_code'],$this->params['academic_year_id']);
                }else{
                  $response = Errors::notFoundError("Route not found!");
                }
              }else{
                $response = Errors::notFoundError("Route not found!");
              }
              break;
            case 'PATCH':
              if(sizeof($this->params) > 0){
                if($this->params['action'] == "distribution"){
                  $response = $this->districtDistribution();
                }elseif($this->params['action'] == "request"){
                  $response = $this->districtRequest();
                }else{
                  $response = Errors::notFoundError("Route not found!");
                }
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

    function districtDistribution(){
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

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
        
        if (!LimitsValidation::distDistribution($input)) {
            return Errors::unprocessableEntityResponse();
        }

        $remainLimitBefore = self::remainLimitsOnRebToDistricts($input);

        if(!self::isExceedLimits($input)){
              $response['status_code_header'] = 'HTTP/1.1 200 success';
              $response['body'] = json_encode([
                "message" => "Distribution is exceeding allocated qualification post!",
                "academic_distributed" => null,
                "total_distributed" => $remainLimitBefore
              ]);
              return $response;
        }

        // Insert into distributed

        $result = $this->postsModel->distributeToSchool($input,$user_id);

        $remainLimits = self::remainLimitsOnRebToDistricts($input);

        $academic_distributed = new \stdClass();

        $academic_distributed->message = "Updated success";
        $academic_distributed->academic_distributed = sizeof($result) > 0 ? $result[0] : null;
        $academic_distributed->total_distributed = $remainLimits;

        $response['status_code_header'] = 'HTTP/1.1 200 success';
        $response['body'] = json_encode($academic_distributed);
        return $response;
 
    }
    function remainLimitsOnRebToDistricts($input){

      $district_limits = $this->rebDistributionsModel->findDistrictQualLimits($input['academic_year_id'],$input['qualification_id'],$input['district_code']);

      $remainLimits = [];
      foreach ($district_limits as $value) {
        $limits =  self::getLimitsByAcademicQualificationAndDistrict($value['academic_year_id'],$value['qualification_id'],$value['district_code'],$value['limits']);
        if($limits){
          array_push($remainLimits,$limits);
        }
      }
      return $remainLimits;
    }
    function isExceedLimits($input){

      $dist_limits = $this->postsModel->findSumOfDistributed($input['qualification_id'],$input['academic_year_id'],$input['district_code']);
      $new_remain = sizeof($dist_limits) > 0 ? ($input['dde_post_distribution'] + $dist_limits[0]['dde_post_distribution']) : $dist_limits + 0;

      $dist_limits_set = $this->rebDistributionsModel->findDistrictQualLimits($input['academic_year_id'],$input['qualification_id'],$input['district_code']);
      $qual_limits = sizeof($dist_limits_set) > 0 ? $dist_limits_set[0]['limits'] : 0;

      $decision = (abs($new_remain) <= $qual_limits) ? true : false;

      return $decision;
    }
    function getLimitsByAcademicQualificationAndDistrict($academic_year_id,$qualification_id,$district_code,$limits){
      $remaining = new \stdClass();

      $reb_limits = $this->postsModel->findSumOfDistributed($district_code,$qualification_id,$academic_year_id);

      $remaining->qualification_id = $qualification_id;
      $remaining->limits = $limits;
      $remaining->remaining_limits = sizeof($reb_limits) > 0 ? ($limits - (abs($reb_limits[0]['dde_post_distribution']))) : ($limits - 0);
      
      return $remaining;
    }
    // End of district distribution to school

    // get distributed post to school by 
    function getDistrictDistributionByAcademic($district_code,$academic_year_id){
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
      $input = [];
      $rlt = new \stdClass();
      
      $input['district_code'] = $district_code;
      $input['academic_year_id'] = $academic_year_id;

      $remainLimit = [];

      $result = $this->postsModel->findDistrictDistribution($district_code,$academic_year_id);
      foreach ($result as $value) {
        $input['qualification_id'] = $value['qualification_id'];
        $remainLimits = self::remainLimitsOnRebToDistricts($input);
        if(sizeof($remainLimits) > 0 ){
          array_push($remainLimit,$remainLimits[0]);
        }
      }
      
      $rlt->academic_distributed = $result;
      $rlt->total_distributed = $remainLimit;

      $response['status_code_header'] = 'HTTP/1.1 200 success';
      $response['body'] = json_encode($rlt);
      return $response;
    }
    // District request to reb
    function districtRequest(){
      $data = (array) json_decode(file_get_contents('php://input'), TRUE);

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

      if (sizeof($data) == 0) {
          return Errors::unprocessableEntityResponse();
      }

      foreach ($data as $value) {
          if(!empty($value)){
            $this->postsModel->findDistrictDistribution($value,$user_id);
          }
      }

      $response['status_code_header'] = 'HTTP/1.1 200 success';
      $response['body'] = json_encode($data);
      return $response;
    }
  }
$controller = new PostsController($this->db, $request_method,$params);
$controller->processRequest();