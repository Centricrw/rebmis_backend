<?php
namespace Src\Models;

class TeacherTransferModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function getSchoolsDataPerDistrict($district_code)
    {
      $statement = " 
      SELECT * FROM 
      schools s
      INNER JOIN school_location sl ON s.region_code = sl.village_id
      WHERE sl.district_code = ?";

      try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array($district_code));
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function teacherRequestATransfer($data, $user_id)
    {
      $statement = "

        INSERT INTO teacher_transfer(
          techer_id, school_from_id, requested_school_id, 
          teacher_reason, teacher_supporting_document, requested_status
        )
            VALUES 
              (:techer_id, :school_from_id, :requested_school_id, 
              :teacher_reason, :teacher_supporting_document, :requested_status
            );
          ";
        try 
        {
          $statement = $this->db->prepare($statement);
          $statement->execute(array(
              ':techer_id' => $user_id,
              ':school_from_id' => $data['school_from_id'],
              ':requested_school_id' => $data['requested_school_id'],
              ':teacher_reason' => $data['teacher_reason'],
              ':teacher_supporting_document' => $data['teacher_supporting_document'],
              ':requested_status' => 'PENDING',
          ));
          return $statement->rowCount();
        } 
        catch (\PDOException $e) {
          exit($e->getMessage());
        } 
    }

    public function incomingDdeTransferDecision($data, $user_id)
    {
      if($data['requested_status'] == 'APPROVED'){
        $approved_school_id  = $data['approved_school_id'];
      }elseif($data['requested_status'] == 'REJECTED'){
        $approved_school_id  =null;
      }
      $sql = "
            UPDATE 
              teacher_transfer
            SET 
            requested_dde_id=:requested_dde_id,requested_status=:requested_status,approved_school_id=:approved_school_id,requested_comment=:requested_comment,	incoming_decision_date=:incoming_decision_date

            WHERE teacher_transfer_id=:teacher_transfer_id;
        ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
              ':requested_dde_id' => $user_id,
              ':requested_status' => $data['requested_status'],
              ':approved_school_id' => $approved_school_id,
              ':requested_comment' => $data['requested_comment'],
              ':incoming_decision_date' => date("Y-m-d"),
              ':teacher_transfer_id' => $data['teacher_transfer_id'],
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function outgoingDdeTransferDecision($data, $user_id)
    {

      $sql = "
            UPDATE 
              teacher_transfer
            SET 

            outgoinng_dde_id=:outgoinng_dde_id,outgoing_status=:outgoing_status,outgoing_dde_comment=:outgoing_dde_comment,	outgoing_decision_date=:outgoing_decision_date

            WHERE teacher_transfer_id=:teacher_transfer_id;
        ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
              ':outgoinng_dde_id' => $user_id,
              ':outgoing_status' => $data['outgoing_status'],
              ':outgoing_dde_comment' => $data['outgoing_dde_comment'],
              ':outgoing_decision_date' => date("Y-m-d"),
              ':teacher_transfer_id' => $data['teacher_transfer_id'],
            ));
            if($data['outgoing_status'] == 'APPROVED'){
              $this->getASchoolToTransferApprovedTeacher($data['teacher_transfer_id']);
            }
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }


    public function getTeacherTreansferRequest($user_id)
    {
      $statement = " 
        SELECT tt.teacher_transfer_id, tt.teacher_supporting_document, 


       

        tt.requested_school_id,
        (
            SELECT s.school_name FROM schools s WHERE s.school_code = tt.requested_school_id
        ) requested_school_name,
        (
            SELECT sl.district_name
          FROM schools s 
          INNER JOIN school_location sl ON sl.village_id = s.region_code
          WHERE s.school_code = tt.requested_school_id
        ) requested_school_district,
        (
            SELECT sl.sector_name
          FROM schools s 
          INNER JOIN school_location sl ON sl.village_id = s.region_code
          WHERE s.school_code = tt.requested_school_id
        ) requested_school_sector,

        tt.approved_school_id,
        (
            SELECT s.school_name FROM schools s WHERE s.school_code = tt.approved_school_id
        ) approved_school_name,

        tt.requested_status, tt.teacher_requested_transfer_date, tt.teacher_reason,
        
        tt.requested_comment, tt.incoming_decision_date, 

        tt.outgoing_status, tt.outgoing_dde_comment, tt.outgoing_decision_date
        FROM teacher_transfer tt
      


        WHERE  tt.techer_id = ?";

      try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array($user_id));
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function getTeacherTreansferRequestForRequestedDde($user_id)
    {
      
       $district_code_array = $this->getDdeDistrictId($user_id);
       $district_code = $district_code_array[0]['district_code'];
      
      $statement = " 
      SELECT tt.teacher_transfer_id, 
      tt.techer_id teacher_id,
              (
                  SELECT u.full_name FROM users u WHERE u.user_id = tt.techer_id
              ) teacher_full_name,
              (
                  SELECT u.first_name FROM users u WHERE u.user_id = tt.techer_id
              ) teacher_first_name,
              (
                  SELECT u.last_name FROM users u WHERE u.user_id = tt.techer_id
              ) teacher_last_name,
              (
                  SELECT u.phone_numbers FROM users u WHERE u.user_id = tt.techer_id
              ) teacher_phone_number,
              
            tt.school_from_id,
            (
                SELECT s.school_name FROM schools s WHERE s.school_code = tt.school_from_id
            ) school_from_name,
    
            (
              SELECT sl.district_name
              FROM schools s 
              INNER JOIN school_location sl ON sl.village_id = s.region_code
              WHERE s.school_code = tt.school_from_id
            ) school_from_district,
            (
                SELECT sl.sector_name
              FROM schools s 
              INNER JOIN school_location sl ON sl.village_id = s.region_code
              WHERE s.school_code = tt.school_from_id
            ) school_from_sector,
            
            tt.teacher_requested_transfer_date,

            tt.requested_school_id,
            (
                SELECT s.school_name FROM schools s WHERE s.school_code = tt.requested_school_id
            ) requested_school_name,
            (
                SELECT sl.district_name
              FROM schools s 
              INNER JOIN school_location sl ON sl.village_id = s.region_code
              WHERE s.school_code = tt.requested_school_id
            ) requested_school_district,
            (
                SELECT sl.sector_name
              FROM schools s 
              INNER JOIN school_location sl ON sl.village_id = s.region_code
              WHERE s.school_code = tt.requested_school_id
            ) requested_school_sector,
            
            tt.requested_status, tt.incoming_decision_date, tt.requested_dde_id, tt.requested_comment,
           
           tt.approved_school_id,
            (
                SELECT s.school_name FROM schools s WHERE s.school_code = tt.approved_school_id
            ) approved_school_name,
            (
                SELECT sl.district_name
              FROM schools s 
              INNER JOIN school_location sl ON sl.village_id = s.region_code
              WHERE s.school_code = tt.approved_school_id
            ) approved_school_district,
            (
                SELECT sl.sector_name
              FROM schools s 
              INNER JOIN school_location sl ON sl.village_id = s.region_code
              WHERE s.school_code = tt.approved_school_id
            ) approved_school_sector,
            
            tt.outgoing_status, tt.outgoing_decision_date, tt.outgoinng_dde_id, tt.outgoing_dde_comment
            
    
            
    
        FROM teacher_transfer tt
        INNER JOIN schools s ON s.school_code = tt.requested_school_id
        INNER JOIN school_location sl ON sl.village_id = s.region_code
        WHERE sl.district_code = ?";

      try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array($district_code));
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function getTeacherTreansferRequestForOutgoingDde($user_id)
    {
      $district_code_array = $this->getDdeDistrictId($user_id);
      $district_code = $district_code_array[0]['district_code'];
     
      $statement = " 

        SELECT tt.teacher_transfer_id, tt.techer_id, 
        (
          SELECT u.full_name FROM users u WHERE u.user_id = tt.techer_id
        ) full_name,
        
        (
          SELECT q.qualification_name
            FROM user_to_role ur
            INNER JOIN qualifications q ON q.qualification_id = ur.qualification_id
            WHERE ur.user_id = tt.techer_id
        ) qualification_name,
        
        (
          SELECT p.position_name
            FROM user_to_role ur
            INNER JOIN positions p ON p.position_id = ur.position_id
            WHERE ur.user_id = tt.techer_id
        ) position_name,

        tt.teacher_reason, tt.teacher_supporting_document,
              tt.requested_school_id, 
              tt.outgoing_status,
          
        (
          SELECT s.school_name FROM schools s WHERE s.school_code = tt.requested_school_id
        ) requested_school_name,
        (
            SELECT sl.district_name
          FROM schools s 
          INNER JOIN school_location sl ON sl.village_id = s.region_code
          WHERE s.school_code = tt.requested_school_id
        ) requested_school_district,
        (
            SELECT sl.sector_name
          FROM schools s 
          INNER JOIN school_location sl ON sl.village_id = s.region_code
          WHERE s.school_code = tt.requested_school_id
        ) requested_school_sector,
        tt.teacher_requested_transfer_date

        

        FROM teacher_transfer tt
        INNER JOIN schools s ON s.school_code = tt.requested_school_id
        INNER JOIN school_location sl ON sl.village_id = s.region_code
        WHERE sl.district_code = ?";

      try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array($district_code));
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    function getDdeDistrictId($ddiId){
      $statement = " 
        SELECT district_code
        FROM user_to_role WHERE
       user_id = ?";

      try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array($ddiId));
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    function getASchoolToTransferApprovedTeacher($teacher_transfer_id)
    {
      $statement = "
          SELECT techer_id, approved_school_id 
          FROM teacher_transfer 
          WHERE teacher_transfer_id= ?
        ";

        try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array($teacher_transfer_id));
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          $techer_id = $result[0]['techer_id'];
          $approved_school_id = $result[0]['approved_school_id'];

          $this->transferTheTeacherToTheApprovedSchool($techer_id, $approved_school_id);

          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    function transferTheTeacherToTheApprovedSchool($techer_id, $approved_school_id)
    {
      $sql = "
            UPDATE 
              user_to_role
            SET 
            school_code=:school_code

            WHERE user_id=:user_id;
        ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
              ':school_code' => $approved_school_id,
              ':user_id' => $techer_id,
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    function teacherLeaving($teacher_transfer_id){
      
      $sql = "
      UPDATE 
      teacher_transfer
      SET 

      teacher_requested_leaving_date= now(),
      outgoing_status = 'PENDING'
      WHERE teacher_transfer_id =:teacher_transfer_id;
        ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
              ':teacher_transfer_id' => $teacher_transfer_id
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}
?>