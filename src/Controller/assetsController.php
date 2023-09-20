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
                } else {
                    $response = $this->getAllAssets();
                }
                break;
            case "POST":
                $response = $this->createNewAssets();
                break;
            case "PUT":
                $response = $this->updateAssets($this->params['id']);
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

}
$controller = new AssetCategoriesController($this->db, $request_method, $params);
$controller->processRequest();
