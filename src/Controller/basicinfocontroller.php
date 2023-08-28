<?php
namespace Src\Controller;
use Src\Models\QualificationsModel;
use Src\Models\AcademicCalenderModel;
use Src\Models\MartialStatusModel;
use Src\Models\BanksModel;
use Src\Models\CountriesModel;
use Src\Models\EducationDomainModel;
use Src\Models\EducationSubdomainModel;
use Src\Models\StaffCategoryModel;
use Src\Models\SpecializationsModel;
use Src\Models\QualificationModel;
use Src\Models\SchoolsModel;
use Src\Models\SchoolLocationsModel;
use Src\Models\RolesModel;
use Src\Models\StakeholdersModel;
use Src\Models\PositionModel;
use Src\System\Errors;
use Src\System\AuthValidation;

class BasicInfoController {
    private $db;
    private $qualificationsModel;
    private $academicCalenderModel;
    private $martialStatusModel;
    private $banksModel;
    private $countriesModel;
    private $educationDomainModel;
    private $educationSubdomainModel;
    private $staffCategoryModel;
    private $specializationsModel;
    private $qualificationModel;
    private $schoolsModel;
    private $schoolLocationModel;
    private $rolesModel;
    private $stakeholdersModel;
    private $positionModel;
    private $request_method;
    private $params;

    public function __construct($db,$request_method,$params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->qualificationsModel = new QualificationsModel($db);
        $this->academicCalenderModel = new AcademicCalenderModel($db);
        $this->martialStatusModel = new MartialStatusModel($db);
        $this->countriesModel = new CountriesModel($db);
        $this->banksModel = new BanksModel($db);
        $this->educationDomainModel = new EducationDomainModel($db);
        $this->educationSubdomainModel = new EducationSubdomainModel($db);
        $this->staffCategoryModel = new StaffCategoryModel($db);
        $this->specializationsModel = new SpecializationsModel($db);
        $this->qualificationModel = new QualificationModel($db);
        $this->schoolsModel = new SchoolsModel($db);
        $this->rolesModel = new RolesModel($db);
        $this->stakeholdersModel = new StakeholdersModel($db);
        $this->positionModel = new PositionModel($db);
        $this->schoolLocationModel = new SchoolLocationsModel($db);
    }
    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if(sizeof($this->params) > 0){
                    if($this->params['action'] == "acalqual"){
                        $response = $this->getAcademicCalenderAndQualifications();
                    }elseif($this->params['action'] == "userpreregister"){
                        $response = $this->getPreregisterData();
                    }elseif($this->params['action'] == "userderegister"){
                        $response = $this->getDeregisterData($this->params['district_code']);
                    }else{
                        $response = Errors::notFoundError("Route not found!");
                    }
                }else{
                    $response = Errors::notFoundError("Route not found!");
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
    function getAcademicCalenderAndQualifications(){
        
            $jwt_data = new \stdClass();
            $output = new \stdClass();

            $all_headers = getallheaders();
            if(isset($all_headers['Authorization'])){
              $jwt_data->jwt = $all_headers['Authorization'];
            }
            // Decoding jwt
            if(empty($jwt_data->jwt)){
              return Errors::notFoundError($jwt_data->jwt);
            }
            if(!AuthValidation::isValidJwt($jwt_data)){
              return Errors::notFoundError($jwt_data->jwt);
            }

            $academic_calender = $this->academicCalenderModel->findCurrentAcademicYear();

            $result = $this->qualificationsModel->findAll();

            $output->jwt = $jwt_data->jwt;
            $output->qualifications = $result;
            $output->academic_calender = sizeof($academic_calender) > 0 ? $academic_calender[0] : null;

            $response['status_code_header'] = 'HTTP/1.1 200 success';
            $response['body'] = json_encode($output);
            return $response;
    }
    function getPreregisterData(){
        
            $jwt_data = new \stdClass();
            $output = new \stdClass();

            $all_headers = getallheaders();
            if(isset($all_headers['Authorization'])){
              $jwt_data->jwt = $all_headers['Authorization'];
            }
            // Decoding jwt
            if(empty($jwt_data->jwt)){
              return Errors::notFoundError($jwt_data->jwt);
            }
            if(!AuthValidation::isValidJwt($jwt_data)){
              return Errors::notFoundError($jwt_data->jwt);
            }
            $marital = $this->martialStatusModel->findAll();
            $output->marital = $marital;
            $banks = $this->banksModel->findAll();
            $output->banks = $banks;
            $nationality =  $this->countriesModel->findAll();
            $output->nationality = $nationality;
            $education_domain = $this->educationDomainModel->findAll();
            $output->education_domain = $education_domain;
            $education_subdomain = $this->educationSubdomainModel->findAll();
            $output->education_sub_domain = $education_subdomain;
            $staff_category = $this->staffCategoryModel->findAll();
            $output->staff_category = $staff_category;
            $specializations = $this->specializationsModel->findAll();
            $output->specializations = $specializations;
            $qualifications = $this->qualificationModel->findAll();
            $output->qualifications = $qualifications;
            $output->contract_types = ["permanent","contractual"];


            $response['status_code_header'] = 'HTTP/1.1 200 success';
            $response['body'] = json_encode($output);
            return $response;
    }
    function getDeregisterData($district_code){

        $jwt_data = new \stdClass();
        $output = new \stdClass();

        $all_headers = getallheaders();
        if(isset($all_headers['Authorization'])){
            $jwt_data->jwt = $all_headers['Authorization'];
        }
        // Decoding jwt
        if(empty($jwt_data->jwt)){
            return Errors::notFoundError($jwt_data->jwt);
        }
        if(!AuthValidation::isValidJwt($jwt_data)){
            return Errors::notFoundError($jwt_data->jwt);
        }

        $output->roles = $this->rolesModel->findAll();
        $output->qualifications = $this->qualificationModel->findAll();
        $output->schools = $this->schoolLocationModel->findDistrictSchools($district_code);
        $output->stakeholders = $this->stakeholdersModel->findAll();
        $output->positions = $this->positionModel->findAll();
        $output->sectors = $this->schoolLocationModel->findDistrictSectors($district_code);

        $response['status_code_header'] = 'HTTP/1.1 200 success';
        $response['body'] = json_encode($output);
        return $response;
    }
    
}

$controller = new BasicInfoController($this->db, $request_method,$params);
$controller->processRequest();
?>