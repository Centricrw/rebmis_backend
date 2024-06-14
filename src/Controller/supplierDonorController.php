<?php
namespace Src\Controller;

use Src\Models\SupplierDonorModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class SupplierDonorController
{
    private $db;
    private $supplierDonorModel;
    private $trainingsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->supplierDonorModel = new SupplierDonorModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                $response = $this->getAllSupplier();
                break;
            case "POST":
                $response = $this->createNewSupplier();
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

}
$controller = new SupplierDonorController($this->db, $request_method, $params);
$controller->processRequest();
