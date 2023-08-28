<?php
namespace Src\Models;

class PostsModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }
  public function insert($data,$user_id){
    $statement = "
    INSERT 
      INTO post_request
        (academic_year_id, school_code, position_code, qualification_id, head_teacher_id, existing_post, head_teacher_post_request, head_teacher_reason_id, district_code, created_by)
      VALUES 
        (:academic_year_id, :school_code, :position_code, :qualification_id, :head_teacher_id, :existing_post, :head_teacher_post_request, :head_teacher_reason_id, :district_code, :created_by);
    ";
    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
          ':academic_year_id' => $data['academic_year_id'],
          ':school_code' => $data['school_code'],
          ':position_code' => $data['position_code'],
          ':qualification_id' => $data['qualification_id'],
          ':head_teacher_id' => $user_id,
          ':existing_post' => $data['existing_post'],
          ':head_teacher_post_request' => $data['head_teacher_post_request'],
          ':head_teacher_reason_id' => $data['head_teacher_reason_id'],
          ':district_code' => $data['district_code'],
          ':created_by' => $user_id
      ));
      return $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function insertTest($data,$user_id){
    $statement = "
    INSERT 
      INTO post_request_test
        (academic_year_id, school_code, position_code, qualification_id, head_teacher_id, existing_post, head_teacher_post_request, head_teacher_reason_id, district_code, created_by)
      VALUES 
        (:academic_year_id, :school_code, :position_code, :qualification_id, :head_teacher_id, :existing_post, :head_teacher_post_request, :head_teacher_reason_id, :district_code, :created_by);
    ";
    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
          ':academic_year_id' => $data['academic_year_id'],
          ':school_code' => $data['school_code'],
          ':position_code' => $data['position_code'],
          ':qualification_id' => $data['qualification_id'],
          ':head_teacher_id' => $user_id,
          ':existing_post' => $data['existing_post'],
          ':head_teacher_post_request' => $data['head_teacher_post_request'],
          ':head_teacher_reason_id' => $data['head_teacher_reason_id'],
          ':district_code' => $data['district_code'],
          ':created_by' => $user_id
      ));
      return $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function update($data,$user_id){
    $sql = "
      UPDATE 
        post_request
      SET 
        head_teacher_post_request=:head_teacher_post_request,head_teacher_reason_id=:head_teacher_reason_id, existing_post=:existing_post, updated_by=:updated_by
      WHERE 
        academic_year_id=:academic_year_id AND school_code=:school_code AND position_code=:position_code AND qualification_id=:qualification_id
    ";
    try {
        $statement = $this->db->prepare($sql);
        $statement->execute(array(
          ':head_teacher_post_request' => $data['head_teacher_post_request'],
          ':head_teacher_reason_id' => $data['head_teacher_reason_id'],
          ':academic_year_id' => $data['academic_year_id'],
          ':school_code' => $data['school_code'],
          ':existing_post' => $data['existing_post'],
          ':position_code' => $data['position_code'],
          ':qualification_id' =>$data['qualification_id'],
          ':updated_by' =>$user_id
        ));

        return $statement->rowCount();
    } catch (\PDOException $e) {
        exit($e->getMessage());
    } 
  }
  public function updateExisting($data){
    $sql = "
      UPDATE 
        post_request_test
      SET 
        existing_post=:existing_post
      WHERE 
        academic_year_id=:academic_year_id AND school_code=:school_code AND position_code=:position_code AND qualification_id=:qualification_id
    ";
    try {
        $statement = $this->db->prepare($sql);
        $statement->execute(array(
          ':academic_year_id' => $data['academic_year_id'],
          ':school_code' => $data['school_code'],
          ':existing_post' => $data['existing_post'],
          ':position_code' => $data['position_code'],
          ':qualification_id' =>$data['qualification_id'],
        ));

        return $statement->rowCount();
    } catch (\PDOException $e) {
        exit($e->getMessage());
    } 
  }
  public function confirmedPostByDistrict($data,$user_id){
    $sql = "
      UPDATE 
        post_request
      SET
        dde_id_request=:dde_id_request,dde_post_request=:dde_post_request,dde_post_request_comment=:dde_post_request_comment,updated_by=:updated_by,updated_date=:updated_date
      WHERE 
        post_request_id=:post_request_id
    ";
    try {
        $statement = $this->db->prepare($sql);
        $statement->execute(array(
          ':post_request_id' => $data['post_request_id'],
          ':dde_id_request' => $user_id,
          ':dde_post_request' => $data['dde_post_request'],
          ':dde_post_request_comment' => $data['dde_post_request_comment'],
          ':updated_by' => $user_id,
          ':updated_date' => date('Y-m-d H:i:s')
        ));

        return $statement->rowCount();
    } catch (\PDOException $e) {
        exit($e->getMessage());
    } 
  }
  public function findSchoolRequests($school_code,$academic_year_id){
      $statement = "
        SELECT
          pr.*, sch.school_name, q.qualification_name, p.position_name, sl.school_level_name
        FROM 
          post_request pr, schools sch, qualifications q, positions p, school_levels sl
        WHERE 
          pr.school_code=? AND pr.academic_year_id=? AND pr.school_code=sch.school_code AND pr.qualification_id=q.qualification_id AND pr.position_code=p.position_code AND p.school_level_id=sl.school_level_id
      ";
      try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($school_code,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
      } catch (\PDOException $e) {
      exit($e->getMessage());
      }
  }
  public function findById($post_request_id){
      $statement = "
        SELECT
          *
        FROM 
          post_request
        WHERE 
          post_request_id=?
      ";
      try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($post_request_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
      } catch (\PDOException $e) {
      exit($e->getMessage());
      }
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
  public function findPostRequestBySchoolPosQualAc($data){
    $sql = "
      SELECT
        *
      FROM 
        post_request 
      WHERE 
        school_code=? AND position_code=? AND qualification_id=? AND academic_year_id=?
    ";
      try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($data['school_code'],$data['position_code'],$data['qualification_id'],$data['academic_year_id']));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
      } catch (\PDOException $e) {
      exit($e->getMessage());
      }

  }
  public function findPostRequestBySchoolPosition($school_code,$position_code,$qualification_id){
    $sql = "
      SELECT
        *
      FROM 
        post_request_test
      WHERE 
        school_code=? AND position_code=? AND qualification_id=?
    ";
      try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($school_code,$position_code,$qualification_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
      } catch (\PDOException $e) {
      exit($e->getMessage());
      }

  }
  public function findDdeRequestSchoolPosition($post_request_id)
  {
    $sql = "
      SELECT
        *
      FROM 
        post_request 
      WHERE 
        post_request_id=?
    ";
      try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($post_request_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
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
      qualification_id, academic_year_id, existing_post, SUM(dde_post_distribution) as dde_post_distribution 
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
        pr.*, sch.school_name, q.qualification_name, p.position_name, prr.reason_name
      FROM 
        post_request pr, schools sch, qualifications q, positions p, post_request_reasons prr
      WHERE 
        pr.district_code=? AND pr.academic_year_id=? AND pr.school_code=sch.school_code AND pr.qualification_id=q.qualification_id AND pr.position_code=p.position_code AND pr.head_teacher_reason_id=prr.reason_id
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
  
  public function findRemainGroupedByQual($district_code,$academic_year_id){
    $statement = "
      SELECT 
        qualification_id, SUM(dde_post_distribution) as remaining 
      FROM 
        post_request 
      WHERE 
        district_code=? AND academic_year_id=? GROUP BY qualification_id, district_code, academic_year_id
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
  public function findRemainGroupedByDistrictQual($position_code,$district_code,$qualification_id,$academic_year_id){
    $statement = "
      SELECT 
        qualification_id, SUM(dde_post_distribution) as remaining 
      FROM 
        post_request 
      WHERE 
        district_code=? AND academic_year_id=? AND qualification_id=? AND position_code=?
      ";
      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($district_code,$academic_year_id,$qualification_id,$position_code));
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
  public function findSchoolRequestTotals($school_code,$academic_year_id){
    $sql = "
      SELECT 
        SUM(head_teacher_post_request) as head_teacher_post_request, SUM(dde_post_distribution) as dde_post_distribution
      FROM 
        post_request
      WHERE school_code=? AND academic_year_id=?
    ";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($school_code,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function findSchoolsRequestGroupByQualification($academic_year_id){
    $sql = "
      SELECT 
        post_request_id,qualification_id,district_code, SUM(existing_post) existing_post, SUM(head_teacher_post_request) ht_post_request, SUM(dde_post_request) dde_post_request
      FROM 
        post_request 
      WHERE 
        academic_year_id=? GROUP BY qualification_id, district_code
    ";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function allowedPost($school_code,$academic_year_id){
  $sql = "
      SELECT 
        pr.qualification_id, pr.position_code, (SUM(IFNull(pr.existing_post,0))+SUM(IFNull(pr.dde_post_distribution,0))) as posts, COUNT(ur.position_code) AS occupied_number, ((SUM(IFNull(pr.existing_post,0))+SUM(IFNull(pr.dde_post_distribution,0))) - COUNT(ur.position_code)) as vacant_posts
      FROM 
        post_request pr, user_to_role ur, school_has_positions shp 
      WHERE 
        ur.school_code=:school_code AND ur.academic_year_id=:academic_year_id AND pr.academic_year_id=:academic_year_id AND pr.school_code=ur.school_code AND pr.position_code=shp.position_code AND ur.position_code=shp.position_code  AND ur.school_code=shp.school_code AND ur.status=:status 
      GROUP 
        BY pr.position_code, pr.qualification_id, ur.position_code, ur.qualification_id
      ";
      try {
        $statement = $this->db->prepare($sql);
        $statement->execute(array(
          ':school_code'=>$school_code,
          ':academic_year_id'=>$academic_year_id,
          ':status'=>"Active"
        ));
        $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $statement;
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
    }
  public function schoolAllowedPost($school_code,$academic_year_id){
    $sql ="
      SELECT 
        position_code,qualification_id,dde_post_distribution, (SUM(IFNull(pr.existing_post,0))+SUM(IFNull(pr.dde_post_distribution,0))) as posts 
      FROM 
        post_request pr WHERE school_code=? AND academic_year_id=? GROUP BY position_code, qualification_id;
    ";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($school_code,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }

  public function schoolPositionAllowedPost($school_code,$position_code,$qualification_id,$academic_year_id){
    $sql ="
    SELECT 
      position_code,qualification_id,existing_post,dde_post_distribution,(existing_post + dde_post_distribution) as posts 
    FROM 
      post_request WHERE school_code=? AND position_code=? AND qualification_id=? AND academic_year_id=?;
    ";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($school_code,$position_code,$qualification_id,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function districtSchoolPosts($school_code,$academic_year_id){
    $sql = "
    SELECT 
      pr.school_code, pr.qualification_id, pr.position_code, (SUM(IFNull(pr.existing_post,0))+SUM(IFNull(pr.dde_post_distribution,0))) as posts 
    FROM post_request pr WHERE district_code=? AND academic_year_id=? GROUP BY district_code, position_code, qualification_id
    ";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($school_code,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function schoolPosts($school_code,$academic_year_id){
    $sql = "
    SELECT 
      pr.school_code, pr.qualification_id, pr.position_code, (SUM(IFNull(pr.existing_post,0))+SUM(IFNull(pr.dde_post_distribution,0))) as posts 
    FROM post_request pr WHERE district_code=? AND academic_year_id=? GROUP BY district_code, position_code, qualification_id
    ";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($school_code,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function districtSchoolPositionOccupation($district_code,$academic_year_id){
    $sql = "
    SELECT 
      pr.school_code, sch.school_name, pr.qualification_id, q.qualification_name, pr.position_code, p.position_name, (SUM(IFNull(pr.existing_post,0))+SUM(IFNull(pr.dde_post_distribution,0))) as posts
    FROM 
      post_request pr, schools sch, qualifications q, positions p
    WHERE 
      pr.district_code=? AND pr.academic_year_id=? AND pr.school_code=sch.school_code AND pr.qualification_id=q.qualification_id AND pr.position_code=p.position_code GROUP BY pr.district_code, pr.position_code, pr.qualification_id
    ";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($district_code,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
  public function districtSchoolPositionOccupations($district_code,$academic_year_id){
    $sql = "
    SELECT 
      pr.school_code, sch.school_name, pr.qualification_id, q.qualification_name, pr.position_code, p.position_name, (IFNull(pr.existing_post,0) + IFNull(pr.dde_post_distribution,0)) as posts
    FROM 
      post_request pr, schools sch, qualifications q, positions p
    WHERE 
      pr.district_code=? AND pr.academic_year_id=? AND pr.school_code=sch.school_code AND pr.qualification_id=q.qualification_id AND pr.position_code=p.position_code GROUP BY pr.district_code, pr.position_code, pr.qualification_id
    ";
    try {
      $statement = $this->db->prepare($sql);
      $statement->execute(array($district_code,$academic_year_id));
      $statement = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $statement;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
}
?>