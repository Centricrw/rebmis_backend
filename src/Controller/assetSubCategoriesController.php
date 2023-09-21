<?php
namespace Src\Controller;

use Src\Models\AssetCategoriesModel;
use Src\Models\AssetSubCategoriesModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class AssetSubCategoriesController
{
    private $db;
    private $assetCategoriesModel;
    private $assetSubCategoriesModel;
    private $trainingsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->assetCategoriesModel = new AssetCategoriesModel($db);
        $this->assetSubCategoriesModel = new AssetSubCategoriesModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (isset($this->params['assets_categories_id'])) {
                    $response = $this->getAllAssetsSubCategoriesByCategoryID($this->params['assets_categories_id']);
                } else {
                    $response = $this->getAllAssetsSubCategories();
                }
                break;
            case "POST":
                $response = $this->createNewAssetsSubCategory();
                break;
            case "PUT":
                $response = $this->updateAssetsSubCategory($this->params['assets_categories_id']);
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

    public function createNewAssetsSubCategory()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if assets category id exists
            $categoryExists = $this->assetCategoriesModel->selectAssetsCategoryById($data['assets_categories_id']);
            if (sizeof($categoryExists) == 0) {
                return Errors::notFoundError("Assets category Id not found!, please try again?");
            }
            // checking if assets sub category name exists
            // Remove whitespaces from both sides of a string
            $assets_sub_categories_name = trim($data['name']);
            $assetsSubCategoriesNameExists = $this->assetSubCategoriesModel->selectAssetsSubCategoryByName(strtolower($assets_sub_categories_name));
            if (sizeof($assetsSubCategoriesNameExists) > 0) {
                return Errors::badRequestError("Assets sub category name already exists, please try again?");
            }
            // Generate assests sub category id
            $generated_assets_subCategory_id = UuidGenerator::gUuid();
            $data['id'] = $generated_assets_subCategory_id;
            $this->assetSubCategoriesModel->insertNewAssetsSubCategory($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "name" => $data['name'],
                "message" => "Assets category created successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all assets category
     * @param NULL
     * @return OBJECT $results
     */
    public function getAllAssetsSubCategories()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetSubCategoriesModel->selectAllAssetsSubCategory();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all assets sub category by category id
     * @param NUMBER $assets_categories_id
     * @return OBJECT $results
     */
    public function getAllAssetsSubCategoriesByCategoryID($assets_categories_id)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetSubCategoriesModel->selectAllAssetsCategoryByCategoryID($assets_categories_id);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Update Assets Category
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function updateAssetsSubCategory($id)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if assets sub category exists
            $subCategoryExists = $this->assetSubCategoriesModel->selectAssetsSubCategoryById($id);
            if (sizeof($subCategoryExists) == 0) {
                return Errors::notFoundError("Assets sub category Id not found!, please try again?");
            }
            // checking if assets category id exists
            $categoryExists = $this->assetCategoriesModel->selectAssetsCategoryById($data['assets_categories_id']);
            if (sizeof($categoryExists) == 0) {
                return Errors::notFoundError("Assets category Id not found!, please try again?");
            }
            // checking if training center name exists
            // Remove whitespaces from both sides of a string
            $assets_sub_categories_name = trim($data['name']);
            if (strtolower($assets_sub_categories_name) !== $subCategoryExists[0]['name']) {
                $assetsCategoriesNameExists = $this->assetSubCategoriesModel->selectAssetsSubCategoryByName(strtolower($assets_sub_categories_name));
                if (sizeof($assetsCategoriesNameExists) > 0) {
                    return Errors::badRequestError("Assets sub category name already exists, please try again?");
                }
            }
            $this->assetSubCategoriesModel->updateAssetsSubCategory($data, $id, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "name" => $data['name'],
                "message" => "Assets category updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new AssetSubCategoriesController($this->db, $request_method, $params);
$controller->processRequest();
