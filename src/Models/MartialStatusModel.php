<?php
namespace Src\Models;

class MartialStatusModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }
    public function findAll()
    {
        $martial_status = ["SINGLE","MARRIED","MARRIED","WIDOWED","DIVORCED","SEPARATED","REGISTERED PARTNERSHIP"];

        return $martial_status;
    }
  public function findById($martial_status_id)
  {
    $martial_status = ["SINGLE","MARRIED","MARRIED","WIDOWED","DIVORCED","SEPARATED","REGISTERED PARTNERSHIP"];


    foreach ($martial_status as $data)
    {
        if ($data == $martial_status_id){
            return $data;
        }
    }

    return [];
  }
}
?>