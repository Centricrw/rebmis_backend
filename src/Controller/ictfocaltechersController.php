<?php
namespace Src\Controller;

use Src\Models\IctfocalteachersModel;
use Src\System\AuthValidation;
use Src\System\Errors;

class ictfocalteachersController
{
    private $db;
    private $ictfocalteachersModel;
    private $request_method;
    private $params;

    public function __construct($db, $request_method, $params)
    {
        $this->db = $db;
        $this->request_method = $request_method;
        $this->params = $params;
        $this->ictfocalteachersModel = new IctfocalteachersModel($db);
    }

    function processRequest()
    {
        switch ($this->request_method) {
            case 'GET':
                if (sizeof($this->params) > 0) {
                    if ($this->params['action'] == "getCandidates") {
                        $response = $this->getCandidates();
                    }
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

    public function getCandidates()
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        try {
            $result = $this->ictfocalteachersModel->getCandidates($data);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode($result);
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
    public function getAllAssetsCategories()
    {
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            $results = $this->assetCategoriesModel->selectAllAssetsCategory();
            $newResults = [];
            foreach ($results as $key => $value) {
                $value['attributes'] = unserialize($value['attributes']);
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
     * Update Assets Category
     * @param OBJECT $data
     * @return OBJECT $results
     */

    public function updateAssetsCategory($assets_categories_id)
    {
        // getting input data
        $data = (array) json_decode(file_get_contents('php://input'), true);
        // geting authorized user id
        $logged_user_id = AuthValidation::authorized()->id;
        try {
            // checking if assets category exists
            $categoryExists = $this->assetCategoriesModel->selectAssetsCategoryById($assets_categories_id);
            if (sizeof($categoryExists) == 0) {
                return Errors::notFoundError("Assets category Id not found!, please try again?");
            }
            // checking if training center name exists
            // Remove whitespaces from both sides of a string
            $assets_categories_name = trim($data['assets_categories_name']);
            if (strtolower($assets_categories_name) !== $categoryExists[0]['assets_categories_name']) {
                $assetsCategoriesNameExists = $this->assetCategoriesModel->selectAssetsCategoryByName(strtolower($assets_categories_name));
                if (sizeof($assetsCategoriesNameExists) > 0) {
                    return Errors::badRequestError("Assets category name already exists, please try again?");
                }
            }
            $this->assetCategoriesModel->updateAssetsCategory($data, $assets_categories_id, $logged_user_id);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "assets_categories_name" => $data['assets_categories_name'],
                "message" => "Assets category updated successfully!",
            ]);
            return $response;
        } catch (\Throwable $th) {
            return Errors::databaseError($th->getMessage());
        }
    }

}
$controller = new ictfocalteachersController($this->db, $request_method, $params);
$controller->processRequest();
