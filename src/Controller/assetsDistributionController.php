<?php
namespace Src\Controller;

use Src\Models\AssetCategoriesModel;
use Src\Models\AssetsDistributionModel;
use Src\Models\AssetsModel;
use Src\Models\AssetSubCategoriesModel;
use Src\Models\BrandsModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class AssetsDistributionController
{
    private $db;
    private $assetCategoriesModel;
    private $assetSubCategoriesModel;
    private $brandsModel;
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
        $this->assetSubCategoriesModel = new AssetSubCategoriesModel($db);
        $this->brandsModel = new BrandsModel($db);
        $this->assetsDistributionModel = new AssetsDistributionModel($db);
        $this->assetsModel = new AssetsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (isset($this->params['action']) && $this->params['action'] == "school") {
                    $response = $this->getSchoolDistributionOnBatchDetails($this->params['id']);
                } else {
                    $response = $this->getAllDistributionBatch();
                }
                break;
            case "POST":
                if (isset($this->params['action']) && $this->params['action'] == "adddefinition") {
                    $response = $this->addBatchDefinition($this->params['id']);
                } else if (isset($this->params['id']) && $this->params['id'] == "school") {
                    $response = $this->createNewSchoolDistribution();
                } else if (isset($this->params['id']) && $this->params['id'] == "numbers") {
                    $response = $this->checkingBatchDistributionLimit();
                } else if (isset($this->params['id']) && $this->params['id'] == "assets") {
                    $response = $this->getAssetsSummary();
                } else {
                    $response = $this->createNewDistributionBatch();
                }
                break;
            case "PUT":
                if (isset($this->params['action']) && $this->params['action'] == "updatedefinition") {
                    $response = $this->updateBatchDefinition($this->params['id']);
                } else {
                    $response = $this->updateDistributionBatch($this->params['id']);
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
     * Create new distribution batch asset
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function createNewDistributionBatch()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // Validate input if not empty
        $validateInputData = self::validateDistributionBatchDetails($data);
        if (!$validateInputData['validated']) {
            return Errors::unprocessableEntityResponse($validateInputData['message']);
        }
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // Generate assests Batch Category Id
            $generatedBatchCategoryId = UuidGenerator::gUuid();
            $data['id'] = $generatedBatchCategoryId;
            $insertBatch = $this->assetsDistributionModel->insertNewDistributionBatch($data, $logged_user_id);

            // if ($insertBatch && sizeof($data['batch_details']) > 0) {
            //     foreach ($data['batch_details'] as $key => $value) {
            //         $generatedBatchDetailsID = UuidGenerator::gUuid();
            //         $value['id'] = $generatedBatchDetailsID;
            //         $value['batch_id'] = $generatedBatchCategoryId;
            //         $insertBatchDetails = $this->assetsDistributionModel->insertNewBatchDetails($value);
            //         if ($insertBatchDetails) {
            //             $this->assetsModel->bookAssetStateByCategory($value, $logged_user_id);
            //         }

            //     }
            // }

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($data);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Create new school distribution asset
     * @param OBJECT $data
     * @return OBJECT $results
     */
    public function checkingBatchDistributionLimit()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // function to add assets number limit
            $sumAssetsNumberLimit = function ($carry, $item) {
                $carry += (int) $item['assets_number_limit'];
                return $carry;
            };

            // function to add batch definition ids
            $batchIds = function ($carry, $item) {
                $carry = $carry == "" ? $item['id'] : $carry . ", " . $item['id'];
                return $carry;
            };

            // function to check batch definition details
            $batchCategory = $this->assetsDistributionModel->selectDistributionBatchByCategory($data);
            if (sizeof($batchCategory) == 0) {
                return Errors::badRequestError("There is no batch found on this category, please try again?");
            }

            // getting school batch number limit
            $ids = array_reduce($batchCategory, $batchIds, "");
            $gettingSchoolDistributionNumber = $this->assetsDistributionModel->selectSchoolDistributionByCategory($ids);

            // adding number limit
            $sumOfAssetsAssignedToschools = array_reduce($gettingSchoolDistributionNumber, $sumAssetsNumberLimit, 0);
            $totalAssetsLimits = array_reduce($batchCategory, $sumAssetsNumberLimit, 0);

            // creating results object
            $results = new \stdClass();
            $results->title = $batchCategory[0]['title'];
            $results->assets_number_limit = $totalAssetsLimits;
            $results->assets_number_remaining = $totalAssetsLimits - $sumOfAssetsAssignedToschools;
            $results->total_distributed = $sumOfAssetsAssignedToschools;
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($results);
            return $response;

        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Create new school distribution asset
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function createNewSchoolDistribution()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // Validate input if not empty
        $validateInputData = self::ValidateDistributionSchoolsData($data);
        if (!$validateInputData['validated']) {
            return Errors::unprocessableEntityResponse($validateInputData['message']);
        }
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            foreach ($data['school_distribution'] as $key => $value) {
                // Generate assests Batch Category Id
                $generatedSchoolDistributionId = UuidGenerator::gUuid();
                $value['assets_school_distribution_id'] = $generatedSchoolDistributionId;
                $this->assetsDistributionModel->insertNewSchoolDistribution($value, $logged_user_id);
            }

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($data);
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
     * getting all school distribution on batch details
     * @param STRING $batchDefinitionId
     * @return OBJECT $results
     */
    public function getSchoolDistributionOnBatchDetails($batchDefinitionId)
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetsDistributionModel->selectAllSchoolDistributionDetails($batchDefinitionId);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting assets details
     * @param NULL
     * @return OBJECT $results
     */
    public function getAssetsSummary()
    {

        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetsDistributionModel->selectAssetsDetails($data);

            // creating results object
            $resultsDetails = new \stdClass();
            $resultsDetails->booked = 0;
            $resultsDetails->available = 0;
            $resultsDetails->assigned = 0;

            foreach ($results as $key => $value) {
                if ($value['asset_state'] == "booked") {
                    $resultsDetails->booked += 1;
                }
                if ($value['asset_state'] == "available") {
                    $resultsDetails->available += 1;
                }
                if ($value['asset_state'] == "assigned") {
                    $resultsDetails->assigned += 1;
                }
            }

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($resultsDetails);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Update batch definition
     * @param STRING $batchDefinitionId
     * @return OBJECT $results
     */

    public function updateBatchDefinition($batchDefinitionId)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // Validate input if not empty
        $validateInputData = self::validateBatchDetails($data, "update");
        if (!$validateInputData['validated']) {
            return Errors::unprocessableEntityResponse($validateInputData['message']);
        }
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;

        try {
            // checking if batch definition exists
            $bacthDefinitionExists = $this->assetsDistributionModel->selectBatchDefinitionBYId($batchDefinitionId);
            if (sizeof($bacthDefinitionExists) == 0) {
                return Errors::notFoundError("Batch definition Id not found!, please try again?");
            }

            // checking if assets in that category exists
            if ($data['assets_number_limit'] != $bacthDefinitionExists[0]['assets_number_limit']) {
                $currentLimit = $bacthDefinitionExists[0]['assets_number_limit'];
                $newLimit = $data['assets_number_limit'];
                $currentIsGreater = $newLimit > $currentLimit ? 0 : 1;
                $deffernce = $newLimit > $currentLimit ? (int) $newLimit - (int) $currentLimit : (int) $currentLimit - (int) $newLimit;
                if (!$currentIsGreater) {
                    // checking if the set limit is eqaul to the stock
                    $assetsCategory = $this->assetsModel->selectAssetsByCategory($data['assets_categories_id']);
                    $data['assets_number_limit'] = $deffernce;
                    if ((int) $deffernce > sizeof($assetsCategory)) {
                        return Errors::badRequestError("Assets number limit exceed which is in stock, please try again?");
                    } else {
                        $this->assetsModel->bookAssetStateByCategory($data, $logged_user_id);
                    }
                } else {
                    $this->assetsModel->bookAssetStateByCategory($data, $logged_user_id, "available");
                }
            }

            $this->assetsDistributionModel->updateBatchDetails($data, $batchDefinitionId);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "New Batch definition updted successfully!",
            ]);
            return $response;

        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Update Add batch definition to batch
     * @param STRING $batchId
     * @return OBJECT $results
     */

    public function addBatchDefinition($batchId)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // Validate input if not empty
        $validateInputData = self::validateBatchDetails($data);
        if (!$validateInputData['validated']) {
            return Errors::unprocessableEntityResponse($validateInputData['message']);
        }
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if batch id
            $bacthExists = $this->assetsDistributionModel->selectDistributionBatchById($batchId);
            if (sizeof($bacthExists) == 0) {
                return Errors::notFoundError("Batch category Id not found!, please try again?");
            }

            $generatedBatchDetailsID = UuidGenerator::gUuid();
            $data['id'] = $generatedBatchDetailsID;
            $data['batch_id'] = $batchId;
            $insertBatchDetails = $this->assetsDistributionModel->insertNewBatchDetails($data);
            if ($insertBatchDetails) {
                $this->assetsModel->bookAssetStateByCategory($data, $logged_user_id);
            }

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($data);
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
            // checking if batch id
            $bacthExists = $this->assetsDistributionModel->selectDistributionBatchById($id);
            if (sizeof($bacthExists) == 0) {
                return Errors::notFoundError("Batch category Id not found!, please try again?");
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

    private function validateBatchDetails($input, $action = "new")
    {
        // ***** validate batch_id ********
        if ($action !== "new") {
            if (empty($input['batch_id'])) {
                return ["validated" => false, "message" => "batch_id is required!, please try again"];
            }
            // checking if batch id
            $bacthExists = $this->assetsDistributionModel->selectDistributionBatchById($input['batch_id']);
            if (sizeof($bacthExists) == 0) {
                return ["validated" => false, "message" => "Batch Id not found!, please try again?"];
            }
        }

        // ***** validate assets_categories_id ********
        if (empty($input['assets_categories_id'])) {
            return ["validated" => false, "message" => "assets_categories_id is required!, please try again"];
        }
        // checking if category exists
        $categoriesExists = $this->assetCategoriesModel->selectAssetsCategoryById($input['assets_categories_id']);
        if (sizeof($categoriesExists) == 0) {
            return ["validated" => false, "message" => "Category id '" . $input['assets_categories_id'] . "' not found, please try again?"];
        }

        // ****** validate assets_sub_categories_id *********
        if (!empty($input['assets_sub_categories_id'])) {
            // checking if category exists
            $categoriesExists = $this->assetSubCategoriesModel->selectAssetsSubCategoryById($input['assets_sub_categories_id']);
            if (sizeof($categoriesExists) == 0) {
                return ["validated" => false, "message" => "Sub category id '" . $input['assets_sub_categories_id'] . "' not found, please try again?"];
            }
        }

        //********* validate specification ***********
        if (empty($input['specification'])) {
            return ["validated" => false, "message" => "specification is required!, please try again"];
        }
        if (isset($input['specification']) && !is_array($input['specification'])) {
            return ["validated" => false, "message" => "specification is Invalid!, please try again"];
        }

        //********* validate brand_id ***********
        if (empty($input['brand_id'])) {
            return ["validated" => false, "message" => "brand_id is required!, please try again"];
        }
        // checking if category exists
        $categoriesExists = $this->brandsModel->selectBrandsById($input['brand_id']);
        if (sizeof($categoriesExists) == 0) {
            return ["validated" => false, "message" => "Brand id '" . $input['brand_id'] . "' not found, please try again?"];
        }

        //***********  validate assets_number_limit ********/
        if (empty($input['assets_number_limit'])) {
            return ["validated" => false, "message" => "assets_number_limit is required!, please try again"];
        }
        // checking if the set limit is eqaul to the stock
        if ($action == "new") {
            $assetsCategory = $this->assetsModel->selectAssetsByCategoryBrandSubCategory($input);
            if ((int) $input['assets_number_limit'] > sizeof($assetsCategory)) {
                return ["validated" => false, "message" => "Assets number limit exceed which is in stock on category id '" . $input['assets_categories_id'] . "', please try again?"];
            }
        }

        return ["validated" => true, "message" => "OK"];
    }

    private function validateDistributionBatchDetails($input)
    {
        // validate title
        if (empty($input['title'])) {
            return ["validated" => false, "message" => "title is required!, please try again"];
        }

        if (sizeof($input['batch_details']) > 0) {
            foreach ($input['batch_details'] as $key => $value) {
                // ***** validate assets_categories_id ********
                if (empty($value['assets_categories_id'])) {
                    return ["validated" => false, "message" => "assets_categories_id is required!, please try again"];
                }
                // checking if category exists
                $categoriesExists = $this->assetCategoriesModel->selectAssetsCategoryById($value['assets_categories_id']);
                if (sizeof($categoriesExists) == 0) {
                    return ["validated" => false, "message" => "Category id '" . $value['assets_categories_id'] . "' not found, please try again?"];
                }

                // ****** validate assets_sub_categories_id *********
                if (!empty($value['assets_sub_categories_id'])) {
                    // checking if category exists
                    $categoriesExists = $this->assetSubCategoriesModel->selectAssetsSubCategoryById($value['assets_sub_categories_id']);
                    if (sizeof($categoriesExists) == 0) {
                        return ["validated" => false, "message" => "Sub category id '" . $value['assets_sub_categories_id'] . "' not found, please try again?"];
                    }
                }

                //********* validate specification ***********
                if (empty($value['specification'])) {
                    return ["validated" => false, "message" => "specification is required!, please try again"];
                }
                if (isset($value['specification']) && !is_array($value['specification'])) {
                    return ["validated" => false, "message" => "specification is Invalid!, please try again"];
                }

                //********* validate brand_id ***********
                if (empty($value['brand_id'])) {
                    return ["validated" => false, "message" => "brand_id is required!, please try again"];
                }
                // checking if category exists
                $categoriesExists = $this->brandsModel->selectBrandsById($value['brand_id']);
                if (sizeof($categoriesExists) == 0) {
                    return ["validated" => false, "message" => "Brand id '" . $value['brand_id'] . "' not found, please try again?"];
                }

                //***********  validate assets_number_limit ********/
                if (empty($value['assets_number_limit'])) {
                    return ["validated" => false, "message" => "assets_number_limit is required!, please try again"];
                }
                // checking if the set limit is eqaul to the stock
                $assetsCategory = $this->assetsModel->selectAssetsByCategoryBrandSubCategory($value);
                if ((int) $value['assets_number_limit'] > sizeof($assetsCategory)) {
                    return ["validated" => false, "message" => "Assets number limit exceed which is in stock on category id '" . $value['assets_categories_id'] . "', please try again?"];
                }
            }
        }
        return ["validated" => true, "message" => "OK"];
    }

    private function ValidateDistributionSchoolsData($input)
    {
        if (empty($input['school_distribution']) || !is_array($input['school_distribution'])) {
            return ["validated" => false, "message" => "Invalid school_distribution!, please try again"];
        }

        if (isset($input['school_distribution']) && is_array($input['school_distribution'])) {
            foreach ($input['school_distribution'] as $key => $value) {
                // validate batch_id
                if (empty($value['batch_id'])) {
                    return ["validated" => false, "message" => "batch_id is required!, please try again"];
                }
                if (isset($value['batch_id'])) {
                    $bacthExists = $this->assetsDistributionModel->selectDistributionBatchById($value['batch_id']);
                    if (sizeof($bacthExists) == 0) {
                        return ["validated" => false, "message" => "Batch category Id not found!, please try again?"];
                    }
                }

                // validate level_code
                if (empty($value['level_code'])) {
                    return ["validated" => false, "message" => "level_code is required!, please try again?"];
                }
                if (isset($value['level_code'])) {
                    $levleExists = $this->assetsDistributionModel->selectLevelsByLevelCode($value['level_code']);
                    if (sizeof($levleExists) == 0) {
                        return ["validated" => false, "message" => "Level code not found!, please try again?"];
                    }
                }

                // validate school_code
                if (empty($value['school_code'])) {
                    return ["validated" => false, "message" => "school_code is required!, please try again?"];
                }
                if (isset($value['school_code'])) {
                    $schoolExists = $this->assetsDistributionModel->selectSchoolBySchoolCode($value['school_code']);
                    if (sizeof($schoolExists) == 0) {
                        return ["validated" => false, "message" => "School code not found!, please try again?"];
                    }
                }

                // validating batch_details_id
                if (empty($value['batch_details_id'])) {
                    return ["validated" => false, "message" => "batch_details_id is required!, please try again?"];
                }
                if (isset($value['batch_details_id'])) {
                    $Exists = $this->assetsDistributionModel->selectBatchDefinitionBYId($value['batch_details_id']);
                    if (sizeof($Exists) == 0) {
                        return ["validated" => false, "message" => "Category id not found!, please try again?"];
                    }
                }

                //! checking if does not excced the limit
                // checking if batch category exists
                $batchCategoryExists = $this->assetsDistributionModel->selectDistributionSchool($value);
                if (sizeof($batchCategoryExists) > 0) {
                    return ["validated" => false, "message" => "This school already has batch distribution, please try again?"];
                }

                // validating assets_number_limit
                if (empty($value['assets_number_limit'])) {
                    return ["validated" => false, "message" => "assets_number_limit is required!, please try again?"];
                }
                if (!empty($value['assets_number_limit']) && !is_numeric($value['assets_number_limit'])) {
                    return ["validated" => false, "message" => "Invalid assets_number_limit must be number!, please try again?"];
                }
            }
        }
        return ["validated" => true, "message" => "OK"];
    }

}
$controller = new AssetsDistributionController($this->db, $request_method, $params);
$controller->processRequest();
