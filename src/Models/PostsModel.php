<?php
namespace Src\Models;

class PostsModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }
  public function distributeToSchool($data,$user_id){
    $statement = "
      UPDATE 
        post_request
      SET
        dde_id_distribution=:dde_id_distribution,dde_post_distribution=:dde_post_distribution,dde_distribution_comment=:dde_distribution_comment,updated_date=:updated_date
      WHERE 
        post_request_id=:post_request_id";

    // Get update row
    $updatedRow = "
    SELECT 
      *
    FROM 
        post_request 
    WHERE 
        post_request_id=:post_request_id";
    try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array(
            ':post_request_id' => $data['post_request_id'],
            ':dde_id_distribution' => $user_id,
            ':dde_post_distribution' => $data['dde_post_distribution'],
            ':dde_distribution_comment' => $data['dde_distribution_comment'],
            ':updated_date' => date('Y-m-d H:i:s'),
        ));

        $updatedRow = $this->db->prepare($updatedRow);
        
        $updatedRow->execute(array(
            ':post_request_id' => $data['post_request_id']
        ));

        $updated = $updatedRow->fetchAll(\PDO::FETCH_ASSOC);

        return $updated;
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
  }
  public function findQualificationAndDistrictRequest($qualification_id,$academic_year_id,$district_code){
    $sql = "
      SELECT
        district_code,qualification_id,academic_year_id,SUM(dde_post_request) as dde_post_request,SUM(head_teacher_post_request) as head_teacher_post_request 
      FROM 
        post_request 
      WHERE 
        qualification_id=? AND academic_year_id=? AND district_code=?
    ";
      try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($qualification_id,$academic_year_id,$district_code));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
      } catch (\PDOException $e) {
      exit($e->getMessage());
      }

  }
  public function findSumOfDistributed($district_code,$qualification_id,$academic_year_id){
    $statement = "
      SELECT
      qualification_id, academic_year_id, SUM(dde_post_distribution) as dde_post_distribution 
      FROM 
        post_request 
      WHERE 
      qualification_id=? AND academic_year_id=? AND district_code=?
      ";
      try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($qualification_id,$academic_year_id,$district_code));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
      } catch (\PDOException $e) {
      exit($e->getMessage());
      }
  }
  public function findDistrictDistribution($district_code,$academic_year_id){
    $statement = "
      SELECT
        pr.*, sch.school_name, q.qualification_name, p.position_name
      FROM 
        post_request pr, schools sch, qualifications q, positions p
      WHERE 
        pr.district_code=? AND pr.academic_year_id=? AND pr.school_code=sch.school_code AND pr.qualification_id=q.qualification_id AND pr.position_code=p.position_code
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($district_code,$academic_year_id,));
        $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $statement;
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
  }
  public function allowedTeacherByQualification($district_code,$school_code,$qualification_id,$academic_year_id){
    $sql = "
      SELECT 
        dde_post_distribution
      FROM 
        post_request
      WHERE district_code=? AND school_code=? AND qualification_id=? AND academic_year_id=?
    ";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($district_code,$school_code,$qualification_id,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
}
?>