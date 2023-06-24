<?php
namespace Src\Models;

class PositionModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function findAll()
    {
      $statement = " 
        SELECT p.position_id, p.position_code, p.position_name, sl.school_level_name, p.qualification_id, q.qualification_name
        FROM positions p  
        INNER JOIN qualifications q ON p.qualification_id = q.qualification_id
        INNER JOIN school_levels sl ON p.school_level_code = sl.school_level_code
      ";
      try {
          $statement = $this->db->query($statement);
          $positionsArray = $statement->fetchAll(\PDO::FETCH_ASSOC);
          $result = $this->removeSelectedPositions($positionsArray);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function removeSelectedPositions($positionsArray)
    {
      $result = [];
      foreach($positionsArray as $positionsObject){
        $position_code = $positionsObject['position_code'];
        $statement = " 
            SELECT * FROM post_request WHERE position_code = ?
          ";
          try {
              $statement = $this->db->prepare($statement);
              $statement->execute(array($position_code));
              $post_request_that_has_this_position_array = $statement->fetchAll(\PDO::FETCH_ASSOC);
              if(sizeof($post_request_that_has_this_position_array) == 0){
                array_push($result, $positionsObject);
              }
              
          } catch (\PDOException $e) {
              exit($e->getMessage());
          }

      }
      return $result;
    }

    public function findOne($position_id)
    {
      $statement = "
        SELECT position_code, position_name FROM positions
        WHERE position_id = 1
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($position_id,0));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if(sizeof($result) == 0){
          return null;
        }
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function insert($data){
      $statement = "
        INSERT 
          INTO positions
            (position_code, position_name, createdB_by)
          VALUES 
            (:position_code, :position_name, :createdB_by);
        ";
        try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array(
              ':position_code' => $data['position_code'],
              ':position_name' => $data['position_name'],
              ':createdB_by' => $data['createdB_by'],
          ));
          return $statement->rowCount();
        } catch (\PDOException $e) {
          exit($e->getMessage());
        }
    }

    public function delete($position_id, $archived_by)
    {
      $sql = "
            UPDATE 
                positions
            SET 
                archive=:archive,archived_by=:archived_by,archived_date=:archived_date
            WHERE position_id=:position_id;
        ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
              ':archive' => 1,
              ':archived_by' => $archived_by,
              ':archived_date' => date("Y-m-d H:i:s"),
              ':position_id' =>$position_id
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        } 
    }
}
?>