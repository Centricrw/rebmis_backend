<?php
namespace Src\Controller;

use DateTime;
use Exception;
use Src\Models\AssetCategoriesModel;
use Src\Models\AssetsRequestModel;
use Src\Models\AssetSubCategoriesModel;
use Src\Models\SchoolsModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

// 'PENDING','RETURNED','APPROVED','REJECTED'

class AssetsRequestController
{
    private $db;
    private $assetCategoriesModel;
    private $assetSubCategoriesModel;
    private $assetsRequestModel;
    private $schoolsModel;
    private $userRoleModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->assetCategoriesModel = new AssetCategoriesModel($db);
        $this->assetSubCategoriesModel = new AssetSubCategoriesModel($db);
        $this->assetsRequestModel = new AssetsRequestModel($db);
        $this->schoolsModel = new SchoolsModel($db);
        $this->userRoleModel = new UserRoleModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (isset($this->params['action']) && $this->params['action'] == "school_visiting_list") {
                    $response = $this->getSchoolVisitingList();
                } else {
                    $response = $this->getAllRequestedAssets();
                }
                break;
            case "POST":
                if (isset($this->params['action']) && $this->params['action'] == "add_school_visit_list") {
                    $response = $this->addSchoolToVisitList();
                } else {
                    $response = $this->createNewRequestAssets();
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

    /**
     * Create new Assets Category
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function createNewRequestAssets()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        $definedUsers = ['SCHOOL', 'TEACHER', 'STUDENT', 'STAFF', 'HEAD_TEACHER'];
        try {
            // checking if school has pending request
            $schoolHasPendingRequest = $this->assetsRequestModel->checkSchoolHasPendingOrReturnedRequest($data);
            if (sizeof($schoolHasPendingRequest) > 0) {
                return Errors::badRequestError("School already has pending request, please try again later?");
            }
            // checking if users exists
            if (!in_array($data['users'], $definedUsers)) {
                return Errors::badRequestError("Users not found must be SCHOOL, TEACHER, STUDENT, STAFF or HEAD_TEACHER, please try again later?");
            }
            // checking if assets category exists
            $assetsCategoryExists = $this->assetCategoriesModel->selectAssetsCategoryById($data['category_id']);
            if (sizeof($assetsCategoryExists) === 0) {
                return Errors::badRequestError("Assets category not found, please try again later?");
            }
            // checking if assets sub category exists
            $assetsSubCategoryExists = $this->assetSubCategoriesModel->selectAssetsSubCategoryById($data['subcategory_id']);
            if (sizeof($assetsSubCategoryExists) === 0) {
                return Errors::badRequestError("Assets sub category not found, please try again later?");
            }
            // checking if school code exists
            $schoolExists = $this->schoolsModel->findByCode($data['school_code']);
            if (sizeof($schoolExists) === 0) {
                return Errors::badRequestError("School code not found, please try again later?");
            }
            // Generate user id
            $assets_request_id = UuidGenerator::gUuid();
            $data['assets_request_id'] = $assets_request_id;
            $this->assetsRequestModel->insertNewRequestAsset($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Assets request submitted successfully!",
                "results" => $data,
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Create new Assets Category
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function getAllRequestedAssets()
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            if (sizeof($user_role) === 0) {
                return Errors::badRequestError("Please login first, please try again later?");
            }

            // if is headteacher logged in
            if ($user_role[0]['role_id'] === "2") {
                $results = $this->assetsRequestModel->getSchoolRequestAsset($user_role[0]['school_code']);
                foreach ($results as $key => $value) {
                    $results[$key]['checklist'] = json_decode($value['checklist']);
                }
                $response['status_code_header'] = 'HTTP/1.1 200 Ok';
                $response['body'] = json_encode($results);
                return $response;
            }

            $results = $this->assetsRequestModel->getAllRequestAsset();
            foreach ($results as $key => $value) {
                $results[$key]['checklist'] = json_decode($value['checklist']);
            }
            $response['status_code_header'] = 'HTTP/1.1 200 Ok';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    function isDate($str)
    {
        try {
            $dt = new DateTime($str);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * add school to visit list
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function addSchoolToVisitList()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            //checking if school already on visiting list
            $schoolOnVisitingList = $this->assetsRequestModel->checkingIfSchoolOnVisitingList($data['assets_request_id']);
            if (sizeof($schoolOnVisitingList) > 0) {
                return Errors::badRequestError("School already on visiting list, please try again later?");
            }
            $schoolHasPendingRequest = $this->assetsRequestModel->getSchoolRequestAssetById($data['assets_request_id']);
            if (sizeof($schoolHasPendingRequest) === 0) {
                return Errors::badRequestError("Assets school request not found, please try again later?");
            }
            if (!$this->isDate($data['visit_time'])) {
                return Errors::badRequestError("Invalid visit date, please try again later?");
            }
            // Generate user id
            $assets_request_details_id = UuidGenerator::gUuid();
            $data['assets_request_details_id'] = $assets_request_details_id;
            $this->assetsRequestModel->addSchoolToVisitingList($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "School added to visiting list successfully!",
                "results" => $data,
            ]);
            return $response;

        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Create new Assets Category
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function getSchoolVisitingList()
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            if (sizeof($user_role) === 0) {
                return Errors::badRequestError("Please login first, please try again later?");
            }

            $results = $this->assetsRequestModel->getSchoolVisitingList();
            foreach ($results as $key => $value) {
                $results[$key]['checklist'] = json_decode($value['checklist']);
            }
            $response['status_code_header'] = 'HTTP/1.1 200 Ok';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new AssetsRequestController($this->db, $request_method, $params);
$controller->processRequest();
