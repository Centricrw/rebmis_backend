<?php
namespace Src\Models;

class ReportsModle {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function getProfile($usertype, $user_id)
    {
        
      $row_data = '[
        "staff_code":"",
        "first_name":"",
        "last_name":"",
        "gender":"",
        "age":"",
        "disability":"",
        "cohort":"",
        "district":"",
        "sector":"",
        "school":"",
        "module":[{
            "module_id":"",
            "module_name":"",
            "progress":"",:
            "grade":"",:
            "timespent":"",:
            "unit":[{
                "unit_id":"",
                "unit_name":"",
                "progress":"",
                "grade":"",
                "timespent":"",
            }]
            
        }]
    ]';
      $data = json_encode($row_data);
      return $data;
    }
}
?>