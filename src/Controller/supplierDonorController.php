<?php
namespace Src\Controller;

use DateTime;
use Src\Models\AssetCategoriesModel;
use Src\Models\AssetsModel;
use Src\Models\AssetSubCategoriesModel;
use Src\Models\BrandsModel;
use Src\Models\SupplierDonorModel;
use Src\Models\UserRoleModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class SupplierDonorController
{
    private $db;
    private $supplierDonorModel;
    private $userRoleModel;
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
        $this->supplierDonorModel = new SupplierDonorModel($db);
        $this->userRoleModel = new UserRoleModel($db);
        $this->assetCategoriesModel = new AssetCategoriesModel($db);
        $this->assetSubCategoriesModel = new AssetSubCategoriesModel($db);
        $this->brandsModel = new BrandsModel($db);
        $this->assetsModel = new AssetsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                $response = $this->getAllSupplier();
                break;
            case "POST":
                if (isset($this->params['id']) && $this->params['id'] == "add_assets") {
                    $response = $this->addNewSuppliedAssets();
                } else if (isset($this->params['id']) && $this->params['id'] == "get_assets") {
                    $response = $this->getAssetsUploadedByUser();
                } else if (isset($this->params['id']) && $this->params['id'] == "get_supplier_assets") {
                    $response = $this->getAssetsUploadedByInstitution();
                } else {
                    $response = $this->createNewSupplier();
                }
                break;
            case "PUT":
                $response = $this->updateSupplierDonor($this->params['id']);
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

    public function createNewSupplier()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if user id is set
            if (!isset($data['user_id']) || empty($data['user_id'])) {
                return Errors::badRequestError("user_id is required, please try again?");
            }
            // checking if type exist
            if (!isset($data['type']) || ($data['type'] !== "SUPPLIER" && $data['type'] !== "DONOR")) {
                return Errors::badRequestError("Type is required and must be SUPPLIER or DONOR, please try again?");
            }
            // checking if name exists
            // Remove white spaces from both sides of a string
            $supplier_name = trim($data['name']);
            $supplierNameExists = $this->supplierDonorModel->selectByName(strtolower($supplier_name));
            if (sizeof($supplierNameExists) > 0) {
                return Errors::badRequestError("Supplier or Donor name already exists, please try again?");
            }
            // Generate id
            $generated_assets_id = UuidGenerator::gUuid();
            $data['id'] = $generated_assets_id;
            $this->supplierDonorModel->insertNewSupplier($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "data" => $data,
                "message" => "Supplier or Donor created successfully!",
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
    public function getAllSupplier()
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->supplierDonorModel->selectAll();
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

    public function updateSupplierDonor($id)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if user id is set
            if (!isset($data['user_id']) || empty($data['user_id'])) {
                return Errors::badRequestError("user_id is required, please try again?");
            }
            // checking if supplier exists
            $supplierExists = $this->supplierDonorModel->selectById($id);
            if (sizeof($supplierExists) == 0) {
                return Errors::notFoundError("Supplier or Donor  Id not found!, please try again?");
            }
            // checking if name exists
            // Remove white spaces from both sides of a string
            $supplier_name = trim($data['name']);
            if (strtolower($supplier_name) !== $supplierExists[0]['name']) {
                $supplierNameExists = $this->supplierDonorModel->selectByName(strtolower($supplier_name));
                if (sizeof($supplierNameExists) > 0) {
                    return Errors::badRequestError("Supplier ot donor name already exists, please try again?");
                }
            }
            $this->supplierDonorModel->updateSupplier($data);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "data" => $data,
                "message" => "Supplier or Donor updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    //TODO: approve assets
    //TODO: and then save it to assets

    /**
     * add new supplied assets
     *
     * @return OBJECT $results
     */
    public function addNewSuppliedAssets()
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        if (is_array($data['assets']) != 1) {
            return Errors::badRequestError("Invalid Assets, please try again?");
        }
        try {
            $user_role = $this->userRoleModel->findCurrentUserRole($logged_user_id);
            if (sizeof($user_role) === 0) {
                return Errors::badRequestError("Please login first, please try again later?");
            }

            // getting supplier information
            $supplierInformation = $this->supplierDonorModel->selectByUser_id($logged_user_id);
            if (sizeof($supplierInformation) === 0) {
                return Errors::badRequestError("Supplier or Donor not found!, please try again later?");
            }

            foreach ($data['assets'] as $key => $value) {
                // checking if serial number exists
                $serialExists = $this->assetsModel->selectAssetsBySerialNumber($value['serial_number']);
                if (sizeof($serialExists) > 0) {
                    return Errors::badRequestError("This assets Serial Number: '" . $value['serial_number'] . "' already exists, please try again?");
                }
                // checking if category exists
                $categoryExists = $this->assetCategoriesModel->selectAssetsCategoryById($value['assets_categories_id']);
                if (sizeof($categoryExists) == 0) {
                    return Errors::badRequestError("On index $key assets Category id not found, please try again?");
                }
                // checking if subcategory exists
                $subCategoryExists = $this->assetSubCategoriesModel->selectAssetsSubCategoryById($value['assets_sub_categories_id']);
                if (sizeof($subCategoryExists) == 0) {
                    return Errors::badRequestError("On index $key assets sub category not found, please try again?");
                }
                // checking if brand exists
                $brandExists = $this->brandsModel->selectBrandsById($value['brand_id']);
                if (sizeof($brandExists) == 0) {
                    return Errors::badRequestError("On index $key assets Brand id not found, please try again?");
                }

                // Validate gender
                // if (!isset($item["confirm_status"]) || !in_array($item["confirm_status"], ['APPROVED', 'REJECTED'])) {
                //     return Errors::badRequestError("On Index '$key' confirm_status must be 'APPROVED' or 'REJECTED'");
                // }

                // Validate users
                if (isset($value["users"]) && !in_array($value["users"], ['SCHOOL', 'TEACHER', 'STUDENT', 'STAFF', 'HEAD_TEACHER'])) {
                    return Errors::badRequestError("On Index '$key' users must be 'SCHOOL', 'TEACHER', 'STUDENT', 'STAFF' or 'HEAD_TEACHER'");
                }

                // Validate condition
                if (!isset($value["condition"]) || !in_array($value["condition"], ['GOOD', 'OBSOLETE', 'BAD'])) {
                    return Errors::badRequestError("On Index '$key' condition must be 'GOOD', 'OBSOLETE' or 'BAD'");
                }

                // Validate other keys
                $requiredKeys = ['name', 'short_description', 'specification', 'price', 'delivery_date', 'warrant_period', 'currency'];

                foreach ($requiredKeys as $requiredKey) {
                    if (!isset($value[$requiredKey]) || empty($value[$requiredKey])) {
                        return Errors::badRequestError("On index '$key' $requiredKey is either not set or empty");
                    }
                }
            }

            foreach ($data['assets'] as $key => $value) {
                // Generate supplied_assets_id
                $supplied_assets_id = UuidGenerator::gUuid();
                $value['supplied_assets_id'] = $supplied_assets_id;
                $value['supplier_id'] = $supplierInformation[0]['id'];
                $value['supplier_name'] = $supplierInformation[0]['name'];
                $this->supplierDonorModel->insertNewSuppliedAssets($value, $logged_user_id);
            }
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "message" => "Assets added successfully!",
                "results" => $data['assets'],
            ]);
            return $response;

        } catch (\Throwable $th) {
            //throw $th;
            return Errors::databaseError($th->getMessage());
        }

    }

    function isValidDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    function formatDate($date)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        $formattedDate = $dateTime->format('Y-m-d H:i:s');
        return $formattedDate;
    }

    function addOneDayToDate($date)
    {
        $newTimestamp = strtotime('+1 day', strtotime($date));
        $newDate = date('Y-m-d H:i:s', $newTimestamp);
        return $newDate; // Output: 2024-01-02 00:00:00
    }

    /**
     * getting all assets category
     * @param NULL
     * @return OBJECT $results
     */
    public function getAssetsUploadedByUser()
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        try {
            if (!isset($data['start_date']) || !$this->isValidDate($data['start_date'])) {
                return Errors::badRequestError("Invalid start_date must be Y-m-d");
            }
            if (!isset($data['end_date']) || !$this->isValidDate($data['end_date'])) {
                return Errors::badRequestError("Invalid start_date must be Y-m-d");
            }
            $start_date = $this->formatDate($data['start_date']);
            $end_date = $this->addOneDayToDate($this->formatDate($data['end_date']));
            $results = $this->supplierDonorModel->getAssetsUploadedBYuser($logged_user_id, $start_date, $end_date);
            foreach ($results as $key => $value) {
                $results[$key]['specification'] = json_decode($value['specification']);
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
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
    public function getAssetsUploadedByInstitution()
    {
        // getting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        try {
            if (!isset($data['start_date']) || !$this->isValidDate($data['start_date'])) {
                return Errors::badRequestError("Invalid start_date must be Y-m-d");
            }
            if (!isset($data['end_date']) || !$this->isValidDate($data['end_date'])) {
                return Errors::badRequestError("Invalid start_date must be Y-m-d");
            }
            if (!isset($data['supplier_id'])) {
                return Errors::badRequestError("supplier_id is required");
            }
            $start_date = $this->formatDate($data['start_date']);
            $end_date = $this->addOneDayToDate($this->formatDate($data['end_date']));
            $results = $this->supplierDonorModel->getAssetsUploadedBYInstitution($data['supplier_id'], $start_date, $end_date);
            foreach ($results as $key => $value) {
                $results[$key]['specification'] = json_decode($value['specification']);
            }
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new SupplierDonorController($this->db, $request_method, $params);
$controller->processRequest();
