<?php
namespace Src\Models;

class PostRequestModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function getSchoolRequests($academicId, $schoolId)
    {
      $statement = "
        SELECT q.qualification_name, p.position_name, prr.reason_name, pr.academic_year_id, pr.school_code, pr.position_code, pr.qualification_id, pr.head_teacher_id, pr.head_teacher_post_request, pr.head_teacher_reason_id, pr.dde_id_request, pr.dde_post_request, pr.dde_id_distribution, pr.dde_post_distribution, pr.dde_distribution_comment, pr.district_code, pr.created_by
        FROM post_request pr
        INNER JOIN qualifications q ON pr.qualification_id = q.qualification_id
        INNER JOIN positions p ON pr.position_code = p.position_code
        INNER JOIN post_request_reasons prr ON pr.head_teacher_reason_id = prr.reason_id
      
        WHERE pr.academic_year_id = ? AND pr.school_code = ?  ";

      try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array($academicId,$schoolId));
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function getSchoolRequestsPerDistrict($academicId, $districtId)
    {
        $statement = " 
        SELECT q.qualification_name, pr.dde_post_request_comment, p.position_name, prr.reason_name, s.school_name, pr.post_request_id, pr.academic_year_id, pr.school_code, pr.position_code, pr.qualification_id, pr.head_teacher_id, pr.head_teacher_post_request, pr.head_teacher_reason_id, pr.dde_id_request, pr.dde_post_request, pr.dde_id_distribution, pr.dde_post_distribution, pr.dde_distribution_comment, pr.district_code, pr.created_by
        FROM post_request pr
        INNER JOIN qualifications q ON pr.qualification_id = q.qualification_id
        INNER JOIN positions p ON pr.position_code = p.position_code
        INNER JOIN schools s ON pr.school_code = s.school_code
        INNER JOIN post_request_reasons prr ON pr.head_teacher_reason_id = prr.reason_id
      
        WHERE academic_year_id = ? AND district_code = ? ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($academicId,$districtId));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function ddeaddarequest($data, $user_id)
    {
        $sql = "
            UPDATE 
                post_request
            SET 
            dde_id_request=:dde_id_request,
            dde_post_request_comment=:dde_post_request_comment, 
            dde_post_request=:dde_post_request, 
            district_code=:district_code,
            updated_by=:updated_by
            WHERE 
            post_request_id=:post_request_id
        ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
              ':post_request_id' => $data['post_request_id'],
              ':dde_post_request_comment' => $data['dde_post_request_comment'],
              ':dde_post_request' => $data['dde_post_request'],
              ':district_code' => $data['district_code'],
              ':dde_id_request' => $user_id,
              ':updated_by' =>$user_id,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        } 
    }


    public function addhdrequest($data, $user_id){
        $statement = "
            SELECT * FROM post_request
            WHERE academic_year_id = ? AND school_code = ? AND position_code = ? AND district_code = ? AND qualification_id = ?
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
            $data['academic_year_id'], 
            $data['school_code'], 
            $data['position_code'], 
            $data['district_code'],
            $data['qualification_id']));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if(sizeof($result) == 0){
                $result = $this->insertHdReauqest($data, $user_id);
            }
            else{
                $result = $this->updateHdRequest($data, $user_id);
            }
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

    }

    public function updateHdRequest($data, $user_id){

        $sql = "
            UPDATE 
                post_request
            SET 
                head_teacher_post_request=:head_teacher_post_request, updated_by=:updated_by
            WHERE 
                academic_year_id=:academic_year_id
                AND school_code=:school_code
                AND position_code=:position_code
                AND qualification_id=:qualification_id;
        ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
              ':head_teacher_post_request' => $data['head_teacher_post_request'],
              ':academic_year_id' => $data['academic_year_id'],
              ':school_code' => $data['school_code'],
              ':position_code' => $data['position_code'],
              ':qualification_id' =>$data['qualification_id'],
              ':updated_by' =>$data['head_teacher_id']
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        } 
    }

    public function insertHdReauqest($data, $user_id){
        $statement = "
        INSERT 
          INTO post_request
            (academic_year_id, school_code, position_code, qualification_id, head_teacher_id, head_teacher_post_request, head_teacher_reason_id, district_code, created_by)
          VALUES 
            (:academic_year_id, :school_code, :position_code, :qualification_id, :head_teacher_id, :head_teacher_post_request, :head_teacher_reason_id, :district_code, :created_by);
        ";
        try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array(
              ':academic_year_id' => $data['academic_year_id'],
              ':school_code' => $data['school_code'],
              ':position_code' => $data['position_code'],
              ':qualification_id' => $data['qualification_id'],
              ':head_teacher_id' => $user_id,
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

}
?>