<?php
namespace Src\Models;

class MinecofinLimitsModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }
    public function insert($data,$created_by){
      $statement = "
        INSERT 
          INTO minecofin_limit 
            (academic_year_id,qualification_id,limits,created_by)
          VALUES (:academic_year_id,:qualification_id,:limits,:created_by);
        ";
        try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array(
              ':academic_year_id' => $data['academic_year_id'],
              ':qualification_id' => $data['qualification_id'],
              ':limits' => $data['limits'],
              ':created_by' => $created_by,
          ));
          return $statement->rowCount();
        } catch (\PDOException $e) {
          exit($e->getMessage());
        }
    }
  public function update($data,$user_id){
    $statement = "
      UPDATE 
          minecofin_limit 
        SET
          limits=:limits,updated_by=:updated_by,updated_date=:updated_date
        WHERE academic_year_id=:academic_year_id AND qualification_id=:qualification_id;
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array(
            ':academic_year_id' => $data['academic_year_id'],
            ':qualification_id' => $data['qualification_id'],
            ':limits' => (int)$data['limits'],
            ':updated_by' => $user_id,
            ':updated_date' => date('Y-m-d H:i:s')
        ));
        return $statement->rowCount();
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
  }
  public function findAll(){
    $sql = "SELECT * FROM minecofin_limit";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute();
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function findAcademicYearLimits($academic_year_id){
    $statement = "
      SELECT *  
        FROM 
          minecofin_limit 
        WHERE academic_year_id=?;
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($academic_year_id));
        $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $statement;
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
  }
  public function findLimitByAcademicYearQualification($academic_year_id,$qualification_id){
    $statement = "
      SELECT *  
        FROM 
          minecofin_limit 
        WHERE academic_year_id=? AND qualification_id=?;
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($academic_year_id,$qualification_id));
        $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $statement;
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
  }
}
?>