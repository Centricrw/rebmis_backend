<?php
namespace Src\Controller;

use Src\Models\AssetCategoriesModel;
use Src\Models\AssetsModel;
use Src\Models\AssetSubCategoriesModel;
use Src\Models\BrandsModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class AssetCategoriesController
{
    private $db;
    private $assetCategoriesModel;
    private $assetSubCategoriesModel;
    private $brandsModel;
    private $assetsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->assetCategoriesModel = new AssetCategoriesModel($db);
        $this->assetSubCategoriesModel = new AssetSubCategoriesModel($db);
        $this->brandsModel = new BrandsModel($db);
        $this->assetsModel = new AssetsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (isset($this->params['action']) && $this->params['action'] == "serial") {
                    $response = $this->getAssetBySerialNUmber($this->params['id']);
                } else if (isset($this->params['action']) && $this->params['action'] == "school") {
                    $response = $this->getSchoolAssetBySchoolCode($this->params['id']);
                } else {
                    $response = $this->getAllAssets();
                }
                break;
            case "POST":
                if (isset($this->params['id']) && $this->params['id'] == "schoolassets") {
                    $response = $this->getSchoolAssetsSummary();
                } else {
                    $response = $this->createNewAssets();
                }
                break;
            case "PUT":
                if (isset($this->params['id']) && $this->params['id'] == "engraving") {
                    $response = $this->createNewAssetsToSchool();
                } else {
                    $response = $this->updateAssets($this->params['id']);
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
     * Create new Assets
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function createNewAssets()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        if (is_array($data['assets']) != 1) {
            return Errors::badRequestError("Invalid Assets, please try again?");
        }
        try {
            foreach ($data['assets'] as $key => $value) {
                // checking if serial number exists
                $serialExists = $this->assetsModel->selectAssetsBySerialNumber($value['serial_number']);
                if (sizeof($serialExists) > 0) {
                    return Errors::badRequestError("On index $key assets Serail Number already exists, please try again?");
                }
                // checking if category exists
                $categoryExists = $this->assetCategoriesModel->selectAssetsCategoryById($value['assets_categories_id']);
                if (sizeof($categoryExists) == 0) {
                    return Errors::badRequestError("On index $key assets Category id not found, please try again?");
                }
                // checking if subcategory exists
                if (isset($value['assets_sub_categories_id']) && $value['assets_sub_categories_id'] !== "") {
                    $subCategoryExists = $this->assetSubCategoriesModel->selectAssetsSubCategoryById($value['assets_sub_categories_id']);
                    if (sizeof($subCategoryExists) == 0) {
                        return Errors::badRequestError("On index $key assets sub category not found, please try again?");
                    }
                }
                // checking if brand exists
                $brandExists = $this->brandsModel->selectBrandsById($value['brand_id']);
                if (sizeof($brandExists) == 0) {
                    return Errors::badRequestError("On index $key assets Brand id not found, please try again?");
                }
                // Generate assests sub category id
                $generated_assets_id = UuidGenerator::gUuid();
                $value['id'] = $generated_assets_id;
                $this->assetsModel->insertNewAsset($value, $logged_user_id);
            }
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Assets added successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all assets
     * @param NULL
     * @return OBJECT $results
     */
    public function getAllAssets()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetsModel->selectAllAssets();
            $newResults = [];
            foreach ($results as $key => $value) {
                $value['specification'] = json_decode($value['specification']);
                array_push($newResults, $value);
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($newResults);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all asset on school
     * @param STRING $schoolCode
     * @return OBJECT $results
     */
    public function getSchoolAssetBySchoolCode($schoolCode)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetsModel->getSchoolAssetsBySchoolCode($schoolCode);
            $newResults = [];
            foreach ($results as $key => $value) {
                $value['specification'] = json_decode($value['specification']);
                array_push($newResults, $value);
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($newResults);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all asset by serial number
     * @param STRING $serialNumber
     * @return OBJECT $results
     */
    public function getAssetBySerialNUmber($serialNumber)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetsModel->selectAssetsBySerialNumber($serialNumber);
            $newResults = [];
            foreach ($results as $key => $value) {
                $value['specification'] = json_decode($value['specification']);
                array_push($newResults, $value);
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($newResults);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Update Assets
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function updateAssets($id)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if assets exists
            $AssetsExists = $this->assetsModel->selectAssetById($id);
            if (sizeof($AssetsExists) == 0) {
                return Errors::notFoundError("Assets Id not found!, please try again?");
            }
            // checking if training center name exists
            if ($data['serial_number'] !== $AssetsExists[0]['serial_number']) {
                $serialNumberExists = $this->assetsModel->selectAssetsBySerialNumber($data['serial_number']);
                if (sizeof($serialNumberExists) > 0) {
                    return Errors::badRequestError("Serial Number already exists, please try again?");
                }
            }
            // checking if category exists
            $categoryExists = $this->assetCategoriesModel->selectAssetsCategoryById($data['assets_categories_id']);
            if (sizeof($categoryExists) == 0) {
                return Errors::badRequestError("Assets Category id not found, please try again?");
            }
            // checking if subcategory exists
            if (isset($data['assets_sub_categories_id']) && $data['assets_sub_categories_id'] !== "") {
                $subCategoryExists = $this->assetSubCategoriesModel->selectAssetsSubCategoryById($data['assets_sub_categories_id']);
                if (sizeof($subCategoryExists) == 0) {
                    return Errors::badRequestError("Assets sub category not found, please try again?");
                }
            }
            // checking if brand exists
            $brandExists = $this->brandsModel->selectBrandsById($data['brand_id']);
            if (sizeof($brandExists) == 0) {
                return Errors::badRequestError("Assets Brand id not found, please try again?");
            }
            $this->assetsModel->updateAsset($data, $id, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Assets updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * get school assets summary
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function getSchoolAssetsSummary()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $schoolSummary = function ($values) {
                $results = $this->assetsModel->getSchoolSchoolAssets($values);
                $CurrentAssets = sizeof($results);
                $results[0]['specification'] = json_decode($results[0]['specification']);
                $results[0]['school_current_assets'] = $CurrentAssets;
                return $results[0];
            };
            $resultsDetails = array_map($schoolSummary, $data['schools']);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($resultsDetails);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Insert new asset to school
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function createNewAssetsToSchool()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // Validate input if not empty
        $validateInputData = self::validateAssignAssetToSchool($data);
        if (!$validateInputData['validated']) {
            return Errors::unprocessableEntityResponse($validateInputData['message']);
        }
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            //! checking if asset already been assigned
            // Generate assests Batch Category Id
            $generatedSchoolAssetsId = UuidGenerator::gUuid();
            $data['school_assets_id'] = $generatedSchoolAssetsId;
            $this->assetsModel->insertNewAssetsToSchool($data, $logged_user_id);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($data);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    private function validateAssignAssetToSchool($input)
    {
        // validate serial_number
        if (empty($input['serial_number'])) {
            return ["validated" => false, "message" => "serial_number is required!, please try again"];
        }
        if (!empty($input['serial_number'])) {
            $serialNumberExists = $this->assetsModel->selectAssetsBySerialNumber($input['serial_number']);
            if (sizeof($serialNumberExists) == 0) {
                return ["validated" => false, "message" => "Serial Number not found, please try again?"];
            }
            if ($serialNumberExists[0]['asset_state'] == "assigned") {
                return ["validated" => false, "message" => "This computer already assigned to a school!, please try again?"];
            }
        }

        // validate assets_tag
        if (empty($input['assets_tag'])) {
            return ["validated" => false, "message" => "assets_tag is required!, please try again"];
        }
        if (!empty($input['assets_tag'])) {
            $tagExists = $this->assetsModel->selectAssetsByTag($input['assets_tag']);
            if (sizeof($tagExists) > 0) {
                return ["validated" => false, "message" => "Asset tag already exists, please try again?"];
            }
        }

        // validate level_code
        if (empty($input['level_code'])) {
            return ["validated" => false, "message" => "level_code is required!, please try again"];
        }
        if (!empty($input['level_code'])) {
            $levelCodeExists = $this->assetsModel->getLevelByCode($input['level_code']);
            if (sizeof($levelCodeExists) == 0) {
                return ["validated" => false, "message" => "Level code not found!, please try again?"];
            }
        }

        // validate school_code
        if (empty($input['school_code'])) {
            return ["validated" => false, "message" => "school_code is required!, please try again"];
        }
        if (!empty($input['school_code'])) {
            $schoolCodeExists = $this->assetsModel->getSchoolByCode($input['school_code']);
            if (sizeof($schoolCodeExists) == 0) {
                return ["validated" => false, "message" => "School Code not found!, please try again?"];
            }
        }

        return ["validated" => true, "message" => "OK"];
    }

}
$controller = new AssetCategoriesController($this->db, $request_method, $params);
$controller->processRequest();
