<?php
namespace Src\Models;

class AuthModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }
    public function insert($data){
      $statement = "
        INSERT 
          INTO auth 
            (user_id,username,password)
          VALUES 
            (:user_id,:username,:password);
        ";
        try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array(
              ':user_id' => $data['user_id'],
              ':username' => $data['username'],
              ':password' => $data['password'],
          ));
          return $statement->rowCount();
        } catch (\PDOException $e) {
          exit($e->getMessage());
        }
    }
    public function findOne($username)
    {
      $statement = "
          SELECT 
              *
          FROM
              auth WHERE username = ? AND status = ?
      ";

      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($username,'Active'));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

  public function delete($user_id,$status)
  {
      $sql = "
          UPDATE 
              auth
          SET 
              status = :status
          WHERE user_id = :user_id;
      ";

      try {
          $statement = $this->db->prepare($sql);
          $statement->execute(array(
            ':user_id' => $user_id,
            ':status' =>$status
          ));

          return $statement->rowCount();
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }    
  }
}
?>