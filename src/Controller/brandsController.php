<?php
namespace Src\Controller;

use Src\Models\BrandsModel;
use Src\System\AuthValidation;
use Src\System\Errors;
use Src\System\UuidGenerator;

class BrandsController
{
    private $db;
    private $brandsModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->brandsModel = new BrandsModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                $response = $this->getAllBrands();
                break;
            case "POST":
                $response = $this->createNewBrand();
                break;
            case "PUT":
                $response = $this->updateBrand($this->params['id']);
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

    public function createNewBrand()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if assets sub brand name exists
            // Remove whitespaces from both sides of a string
            $assets_brand_name = trim($data['name']);
            $brandNameExists = $this->brandsModel->selectBrandsByName(strtolower($assets_brand_name));
            if (sizeof($brandNameExists) > 0) {
                return Errors::badRequestError("Assets brand name already exists, please try again?");
            }
            // Generate brand id
            $generated_brand_id = UuidGenerator::gUuid();
            $data['id'] = $generated_brand_id;
            $this->brandsModel->insertNewBrand($data, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "name" => $data['name'],
                "message" => "Brand created successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * getting brands
     * @param NULL
     * @return OBJECT $results
     */
    public function getAllBrands()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->brandsModel->selectAllBrands();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($results);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

    /**
     * Update Brand
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function updateBrand($id)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if Brand exists
            $brandExists = $this->brandsModel->selectBrandsById($id);
            if (sizeof($brandExists) == 0) {
                return Errors::notFoundError("Assets brand Id not found!, please try again?");
            }
            // checking if brand name exists
            // Remove whitespaces from both sides of a string
            $assets_brand_name = trim($data['name']);
            if (strtolower($assets_brand_name) !== $brandExists[0]['name']) {
                $brandNameExists = $this->brandsModel->selectBrandsByName(strtolower($assets_brand_name));
                if (sizeof($brandNameExists) > 0) {
                    return Errors::badRequestError("Brand name already exists, please try again?");
                }
            }
            $this->brandsModel->updateBrand($data, $id, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "name" => $data['name'],
                "message" => "Brand updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new BrandsController($this->db, $request_method, $params);
$controller->processRequest();
