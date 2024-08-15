<?php
namespace Src\Models;

class PositionsModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function findAll()
    {
      $statement = " 
        SELECT 
          p.position_id, p.position_code, p.position_name, sl.school_level_name, p.qualification_id, q.qualification_name
        FROM 
          positions p,qualifications q, school_levels sl 
        WHERE 
            p.qualification_id=q.qualification_id AND p.school_level_id=sl.school_level_id
      ";
      try {
        $statement = $this->db->query($statement);

        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $results;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
    public function findByCode($position_code)
    {
      $statement = "
        SELECT 
          position_code, position_name 
        FROM 
          positions
        WHERE 
          position_code = ?
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($position_code));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
    public function findOne($position_id)
    {
      $statement = "
        SELECT 
        p.position_id, p.position_code, p.position_name, sl.school_level_name, p.qualification_id, q.qualification_name
        FROM 
        positions p,qualifications q, school_levels sl 
        WHERE 
            p.qualification_id=q.qualification_id AND p.school_level_id=sl.school_level_id AND position_id=:position_id AND archive=:archive
      ";
      try {
        $statement = $this->db->prepare($statement);

        $statement->execute(array(
            ':position_id' => $position_id,
            ':archive' => 0
        ));

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if(sizeof($result) == 0){
          return null;
        }
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }
}
?>