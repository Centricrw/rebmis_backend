<?php
namespace Src\Controller;

use Src\Models\AssetCategoriesModel;
use Src\Models\AssetsModel;
use Src\Models\AssetSubCategoriesModel;
use Src\Models\BrandsModel;
use Src\Models\SchoolsModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class migrationController
{
    private $db;
    private $assetCategoriesModel;
    private $assetSubCategoriesModel;
    private $brandsModel;
    private $assetsModel;
    private $schoolsModel;
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
        $this->schoolsModel = new SchoolsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case "GET":
                $response = $this->getAllAssetsUploadedByUser($this->params['school_code'], $this->params['page']);
                break;
            case "POST":
                $response = $this->insertMigrationAssets();
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

    public function insertMigrationAssets()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        if (is_array($data['assets']) != 1) {
            return Errors::badRequestError("Invalid Assets, please try again?");
        }
        try {
            foreach ($data['assets'] as $key => $value) {
                // checking if serial number exists
                $serialExists = $this->assetsModel->selectAssetsBySerialNumber($value['serial_number']);
                if (sizeof($serialExists) > 0) {
                    return Errors::badRequestError("This assets Serial Number: '" . $value['serial_number'] . "' already exists, please try again?");
                }
                // checking if school exists
                $schoolExists = $this->schoolsModel->findByCode($value['school_code']);
                if (sizeof($schoolExists) == 0) {
                    return Errors::notFoundError("This School Code: '" . $value['school_code'] . "' not found!, please try again?");
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
            }

            foreach ($data['assets'] as $key => $value) {
                // checking if assets tag exists
                $assetsExists = $this->assetsModel->selectAssetsByEngravingCodeLimit($value['assets_tag']);
                if (sizeof($assetsExists) > 0) {
                    $value['assets_tag'] = $value['assets_tag'] . "__" . $value['serial_number'];
                }
                // Generate assets sub category id
                $generated_assets_id = UuidGenerator::gUuid();
                $value['id'] = $generated_assets_id;
                $this->assetsModel->insertMigratedAsset($value, $logged_user_id);
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
     * getting all assets uploaded by user
     * @param NULL
     * @return OBJECT $results
     */
    public function getAllAssetsUploadedByUser($schoolCode, $page = 1)
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetsModel->selectAssetsUploadedByUser($logged_user_id, $schoolCode, $page);
            foreach ($results['assets'] as $key => $value) {
                $results['assets'][$key]['specification'] = json_decode($value['specification']);
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new migrationController($this->db, $request_method, $params);
$controller->processRequest();
