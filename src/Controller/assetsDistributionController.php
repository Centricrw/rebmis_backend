<?php
namespace Src\Controller;

use Src\Models\AssetCategoriesModel;
use Src\Models\AssetsDistributionModel;
use Src\Models\AssetsModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class AssetsDistributionController
{
    private $db;
    private $assetCategoriesModel;
    private $assetsDistributionModel;
    private $assetsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->assetCategoriesModel = new AssetCategoriesModel($db);
        $this->assetsDistributionModel = new AssetsDistributionModel($db);
        $this->assetsModel = new AssetsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                $response = $this->getAllDistributionBatch();
                break;
            case "POST":
                $response = $this->createNewDistributionBatch();
                break;
            case "PUT":
                $response = $this->updateDistributionBatch($this->params['id']);
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
     * Create new distribution batch asset
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function createNewDistributionBatch()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if category exists
            $categoriesExists = $this->assetCategoriesModel->selectAssetsCategoryById($data['assets_categories_id']);
            if (sizeof($categoriesExists) == 0) {
                return Errors::badRequestError("Category id not found, please try again?");
            }
            // checking if the set limit is eqaul to the stock
            $assetsCategory = $this->assetsModel->selectAssetsByCategory($data['assets_categories_id']);
            if ((int) $data['assets_number_limit'] > sizeof($assetsCategory)) {
                return Errors::badRequestError("Assets number limit exceed which is in stock, please try again?");
            }
            // checking if batch category exists
            $batchCategoryExists = $this->assetsDistributionModel->selectDistributionBatchByCategory($data['assets_categories_id']);
            if (sizeof($batchCategoryExists) > 0) {
                return Errors::badRequestError("This batch category already exists, please try again?");
            }
            // Generate assests Batch Category Id
            $generatedBatchCategoryId = UuidGenerator::gUuid();
            $data['id'] = $generatedBatchCategoryId;
            $insertBatch = $this->assetsDistributionModel->insertNewDistributionBatch($data, $logged_user_id);
            if ($insertBatch == 1) {
                $this->assetsModel->bookAssetStateByCategory($data['assets_categories_id'], $logged_user_id, (int) $data['assets_number_limit']);
            }
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Batch category created successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting all Batch category
     * @param NULL
     * @return OBJECT $results
     */
    public function getAllDistributionBatch()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetsDistributionModel->selectAllDistributionBatch();
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

    public function updateDistributionBatch($id)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if batch category exists
            $bacthExists = $this->assetsDistributionModel->selectDistributionBatchById($id);
            if (sizeof($bacthExists) == 0) {
                return Errors::notFoundError("Batch category Id not found!, please try again?");
            }
            // checking if batch category exists
            if ($data['assets_categories_id'] != $bacthExists[0]['assets_categories_id']) {
                $batchCategoryExists = $this->assetsDistributionModel->selectDistributionBatchByCategory($data['assets_categories_id']);
                if (sizeof($batchCategoryExists) > 0) {
                    return Errors::badRequestError("This batch category already exists, please try again?");
                }
            }
            // checking if assets in that category exists
            if ($data['assets_number_limit'] != $bacthExists[0]['assets_number_limit']) {
                $currentLimit = $bacthExists[0]['assets_number_limit'];
                $newLimit = $data['assets_number_limit'];
                $currentIsGreater = $newLimit > $currentLimit ? 0 : 1;
                $deffernce = $newLimit > $currentLimit ? (int) $newLimit - (int) $currentLimit : (int) $currentLimit - (int) $newLimit;
                if (!$currentIsGreater) {
                    // checking if the set limit is eqaul to the stock
                    $assetsCategory = $this->assetsModel->selectAssetsByCategory($data['assets_categories_id']);
                    if ((int) $deffernce > sizeof($assetsCategory)) {
                        return Errors::badRequestError("Assets number limit exceed which is in stock, please try again?");
                    } else {
                        $this->assetsModel->bookAssetStateByCategory($data['assets_categories_id'], $logged_user_id, (int) $deffernce);
                    }
                } else {
                    $this->assetsModel->bookAssetStateByCategory($data['assets_categories_id'], $logged_user_id, (int) $deffernce, "available");
                }
            }
            $this->assetsDistributionModel->updateDistributionBatch($data, $id, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Batch category updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new AssetsDistributionController($this->db, $request_method, $params);
$controller->processRequest();
