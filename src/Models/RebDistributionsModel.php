<?php
namespace Src\Models;

class RebDistributionsModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }
    public function insert($data,$user_id){
        $statement = "
            INSERT 
            INTO reb_distributions 
                (academic_year_id,minecofin_limit_id,district_code,qualification_id,limits,created_by,distribution_comment)
            VALUES (:academic_year_id,:minecofin_limit_id,:district_code,:qualification_id,:limits,:created_by,:distribution_comment);
            ";
        $insertRow = "
        SELECT 
          rd.*, d.district_name
        FROM 
          reb_distributions rd , school_location d 
        WHERE 
            rd.academic_year_id=:academic_year_id AND rd.qualification_id=:qualification_id AND rd.district_code=:district_code AND rd.district_code=d.district_code";
        try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array(
              ':academic_year_id' => $data['academic_year_id'],
              ':minecofin_limit_id' => $data['minecofin_limit_id'],
              ':district_code' => $data['district_code'],
              ':qualification_id' => $data['qualification_id'],
              ':limits' => $data['limits'],
              ':created_by' => $user_id,
              ':distribution_comment' => $data['distribution_comment']
          ));

          $insertedData = $this->db->prepare($insertRow);
          $insertedData->execute(array(
            ':academic_year_id' => $data['academic_year_id'],
            ':qualification_id' => $data['qualification_id'],
            ':district_code' => $data['district_code']
        ));

        $inserted = $insertedData->fetchAll(\PDO::FETCH_ASSOC);

          return $inserted;

        } catch (\PDOException $e) {
          exit($e->getMessage());
        }
    }
  public function update($data,$user_id){
    $statement = "
      UPDATE 
        reb_distributions 
        SET
          limits=:limits,updated_by=:updated_by,updated_date=:updated_date,distribution_comment=:distribution_comment
        WHERE academic_year_id=:academic_year_id AND qualification_id=:qualification_id AND district_code=:district_code";
    $insertRow = "
    SELECT 
      rd.*, d.district_name
    FROM 
      reb_distributions rd , school_location d 
    WHERE 
        rd.academic_year_id=:academic_year_id AND rd.qualification_id=:qualification_id AND rd.district_code=:district_code AND rd.district_code=d.district_code LIMIT 1";
    try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array(
            ':academic_year_id' => $data['academic_year_id'],
            ':qualification_id' => $data['qualification_id'],
            ':district_code' => $data['district_code'],
            ':limits' => (int)$data['limits'],
            ':updated_by' => $user_id,
            ':distribution_comment' => $data['distribution_comment'],
            ':updated_date' => date('Y-m-d H:i:s')
        ));

        $insertedData = $this->db->prepare($insertRow);
        
        $insertedData->execute(array(
            ':academic_year_id' => $data['academic_year_id'],
            ':qualification_id' => $data['qualification_id'],
            ':district_code' => $data['district_code']
        ));

        $inserted = $insertedData->fetchAll(\PDO::FETCH_ASSOC);

        return $inserted;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }
  public function findAll(){
    $sql = "SELECT * FROM reb_distributions";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute();
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function findDistributionByAcademicYear($academic_year_id){
    $statement = "
      SELECT 
        DISTINCT rd.*, d.district_code, d.district_name
      FROM 
        reb_distributions rd , school_location d 
      WHERE 
        rd.academic_year_id=? AND rd.district_code=d.district_code";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($academic_year_id));
        $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $statement;
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
  }
  public function findDistrictQualLimits($academic_year_id,$qualification_id,$district_code){
    $statement = "
      SELECT *  
        FROM 
            reb_distributions 
        WHERE academic_year_id=? AND qualification_id=? AND district_code=?;
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($academic_year_id,$qualification_id,$district_code));
        $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $statement;
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
    }
    public function findDistrictLimits($district_code,$academic_year_id){
    $statement = "
        SELECT *  
        FROM 
            reb_distributions 
        WHERE district_code=? AND academic_year_id=?;
        ";
        try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($district_code,$academic_year_id));
        $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $statement;
        } catch (\PDOException $e) {
        exit($e->getMessage());
        }
    }
  public function findSumOfDistributed($qualification_id,$academic_year_id){
    $statement = "
      SELECT
        qualification_id, academic_year_id, SUM(limits) as limits 
      FROM 
        reb_distributions 
      WHERE 
        qualification_id=? AND academic_year_id=?
      ";
      try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($qualification_id,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
      } catch (\PDOException $e) {
      exit($e->getMessage());
      }
  }
  public function findSumOfDistributedOnDistrict($district_code,$qualification_id,$academic_year_id){
    $statement = "
      SELECT
        qualification_id, academic_year_id, SUM(limits) as limits 
      FROM 
        reb_distributions 
      WHERE 
        qualification_id=? AND academic_year_id=? AND district_code=?
      ";
      try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($district_code,$qualification_id,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
      } catch (\PDOException $e) {
      exit($e->getMessage());
      }
  }
}
?>