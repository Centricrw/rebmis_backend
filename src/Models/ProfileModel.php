<?php
namespace Src\Models;

class ProfileModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function getProfile($usertype, $user_id)
    {
        
      $statement = " 
      SELECT staff_code, full_name, sex, dob, marital_status, nid,
      email, phone_numbers, hired_date
      FROM users WHERE user_id = $user_id ";

      try {
          $statement = $this->db->query($statement);
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
}
?>