<?php
namespace Src\Controller;

use Src\Models\RebDistributionsModel;
use Src\Models\MinecofinLimitsModel;
use Src\Models\PostsModel;
use Src\System\Errors;
use Src\System\AuthValidation;
use Src\Validations\LimitsValidation;
use Src\System\UuidGenerator;


  class RebDistributionsModelController {
    private $db;
    private $rebDistributionsModelModel;
    private $minecofinLimitsModel;
    private $postsModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->rebDistributionsModelModel = new RebDistributionsModel($db);
        $this->minecofinLimitsModel = new MinecofinLimitsModel($db);
        $this->postsModel = new PostsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if(sizeof($this->params) > 0){
                    if($this->params['action' ]== 'academic'){
                        $response = $this->getLimitsByAcademicYear($this->params['academic_year_id']);
                    }elseif($this->params['action' ]== 'district'){
                        $response = $this->getLimitsByDistrict($this->params['district_code'],$this->params['academic_year_id']);
                    }
                }else{
                    $response = Errors::notFoundError("Route not found!");
                }
                break;
                case 'POST':
                    $response = $this->createRebDistribution();
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

    function createRebDistribution(){
        
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
        
        if (!LimitsValidation::rebDistribution($input)) {
            return Errors::unprocessableEntityResponse();
        } 

        $remainLimitBefore = self::remainLimitsOnRebToDistricts($input);


        if(!self::isExceedLimits($input['academic_year_id'],$input['qualification_id'],$input['limits'])){
          $response['status_code_header'] = 'HTTP/1.1 200 success';
          $response['body'] = json_encode([
            "message" => "Distribution is exceeding allocated qualification post!",
            "academic_distributed" => null,
            "total_distributed" => $remainLimitBefore
          ]);
          return $response;
        }

        $academic_distributed = new \stdClass();

        $result = [];
        $is_exist = $this->rebDistributionsModelModel->findDistrictLimits($input['district_code'],$input['academic_year_id'],$input['qualification_id']);
        if(sizeof($is_exist) > 0){
            $result = $this->rebDistributionsModelModel->update($input,$user_id);
        }else{
           $result = $this->rebDistributionsModelModel->insert($input,$user_id);
        }
        
        $remainLimits = self::remainLimitsOnRebToDistricts($input);

        $academic_distributed->message ="Updated successful";
        $academic_distributed->academic_distributed = sizeof($result) > 0 ? $result[0] : null;
        $academic_distributed->total_distributed = $remainLimits;

        $response['status_code_header'] = 'HTTP/1.1 200 Created';
        $response['body'] = json_encode($academic_distributed);
        return $response;
    }
    function remainLimitsOnRebToDistricts($input){
      
      $reb_limits = $this->minecofinLimitsModel->findAcademicYearLimits($input['academic_year_id']);

      $remainLimits = [];
      foreach ($reb_limits as $value) {
       array_push($remainLimits,self::getLimitsByAcademicAndQualification($value['academic_year_id'],$value['qualification_id'],$value['limits']));
      }
      return $remainLimits;
    }

    function getLimitsByAcademicYear($academic_year_id){
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

            $reb_limits = $this->minecofinLimitsModel->findAcademicYearLimits($academic_year_id);

            $remainLimits = [];
            foreach ($reb_limits as $value) {
              array_push($remainLimits,self::getLimitsByAcademicAndQualification($value['academic_year_id'],$value['qualification_id'],$value['limits']));
            }
            $result = [];
            $rlt = $this->rebDistributionsModelModel->findDistributionByAcademicYear($academic_year_id);
            foreach ($rlt as $value) {
              $grouped = $this->postsModel->findQualificationAndDistrictRequest($value['qualification_id'],$value['academic_year_id'],$value['district_code']);
              $value['dde_post_request'] = sizeof($grouped) > 0 ? $grouped[0]['dde_post_request'] : 0;
              $value['head_teacher_post_request'] = sizeof($grouped) > 0 ? $grouped[0]['head_teacher_post_request'] : 0;
              array_push($result,$value);
            }

            $academic_distributed = new \stdClass();

            $academic_distributed->academic_distributed = $result;
            $academic_distributed->total_distributed = $remainLimits;

            $response['status_code_header'] = 'HTTP/1.1 200 success';
            $response['body'] = json_encode($academic_distributed);
            return $response;
    }
    function isExceedLimits($academic_year_id,$qualification_id,$limits){

      $reb_limits = $this->rebDistributionsModelModel->findSumOfDistributed($qualification_id,$academic_year_id);
      $new_remain = sizeof($reb_limits) > 0 ? ($limits + $reb_limits[0]['limits']) : $limits + 0;

      $minecofin_limits = $this->minecofinLimitsModel->findLimitByAcademicYearQualification($academic_year_id,$qualification_id);
      $qual_limits = sizeof($minecofin_limits) > 0 ? $minecofin_limits[0]['limits'] : 0;

      $decision = $new_remain <= $qual_limits ? true : false;

      return $decision;
    }
    function getLimitsByAcademicAndQualification($academic_year_id,$qualification_id,$limits){
      $remaining = new \stdClass();


      $reb_limits = $this->rebDistributionsModelModel->findSumOfDistributed($qualification_id,$academic_year_id);

      $remaining->qualification_id = $qualification_id;
      $remaining->limits = $limits;
      $remaining->remaining_limits = sizeof($reb_limits) > 0 ? ($limits - $reb_limits[0]['limits']) : ($limits - 0);
      
      return $remaining;
    }
    function getLimitsByDistrict($district_code,$academic_year_id){
        
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

            $result = $this->rebDistributionsModelModel->findDistrictLimits($district_code,$academic_year_id);
        
            $response['status_code_header'] = 'HTTP/1.1 200 success';
            $response['body'] = json_encode($result);
            return $response;
    }
    function getLimitsByDistrictQualification($district_code,$qualification_id,$academic_year_id){
        
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

            $result = $this->rebDistributionsModelModel->findDistrictQualLimits($academic_year_id,$district_code,$qualification_id);
        
            $response['status_code_header'] = 'HTTP/1.1 200 success';
            $response['body'] = json_encode($result);
            return $response;
    }
}
$controller = new RebDistributionsModelController($this->db, $request_method,$params);
$controller->processRequest();
?>