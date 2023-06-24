<?php
namespace Src\Models;

class PlanModel {

    private $db = null;

    public function __construct($db)
    {
      $this->db = $db;
    }

    public function findAll()
    {
      $statement = " 
        SELECT  * FROM academic_calendar ";

      try {
          $statement = $this->db->query($statement);
          $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
          return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function findOne($calendar_Id)
    {
      $statement = "
        SELECT * FROM academic_calendar
        WHERE academic_year_id = ? AND archive = ?
      ";

      try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array($calendar_Id,0));
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        //$result[0]['testvaluable'] = 'testvalue';
        if(sizeof($result) == 0){
          return null;
        }
        return $result;
      } catch (\PDOException $e) {
          exit($e->getMessage());
      }
    }

    public function insert($data, $user_id){
      $statement = "
        INSERT 
          INTO academic_calendar 
            (academic_year_name, academic_year_description, academic_year_start, 
            academic_year_end, post_request_start, post_request_end, transfer_request_start,transfer_request_end, internal_transfer_assessment_start, internal_transfer_assessment_end, external_transfer_assessment_start, external_transfer_assessment_end, teacher_recruitment_start, teacher_recruitment_end, createdB_by)
          VALUES 
            (:academic_year_name, :academic_year_description, :academic_year_start, 
            :academic_year_end, :post_request_start, :post_request_end, :transfer_request_start,
            :transfer_request_end, :internal_transfer_assessment_start, :internal_transfer_assessment_end, :external_transfer_assessment_start, :external_transfer_assessment_end, :teacher_recruitment_start, :teacher_recruitment_end, :createdB_by
          );
        ";
        try {
          $statement = $this->db->prepare($statement);
          $statement->execute(array(
              ':academic_year_name' => $data['academic_year_name'],
              ':academic_year_description' => $data['academic_year_description'],
              ':academic_year_start' => $data['academic_year_start'],
              ':academic_year_end' => $data['academic_year_end'],
              ':post_request_start' => $data['post_request_start'],
              ':post_request_end' => $data['post_request_end'],
              ':transfer_request_start' => $data['transfer_request_start'],
              ':transfer_request_end' => $data['transfer_request_end'],
              ':internal_transfer_assessment_start' => $data['internal_transfer_assessment_start'],
              ':internal_transfer_assessment_end' => $data['internal_transfer_assessment_end'],
              ':external_transfer_assessment_start' => $data['external_transfer_assessment_start'],
              ':external_transfer_assessment_end' => $data['external_transfer_assessment_end'],
              ':teacher_recruitment_start' => $data['teacher_recruitment_start'],
              ':teacher_recruitment_end' => $data['teacher_recruitment_end'],
              ':createdB_by' => $user_id,
          ));
          return $statement->rowCount();
        } catch (\PDOException $e) {
          exit($e->getMessage());
        }
    }

    public function delete($plan_id, $archived_by)
    {
      $sql = "
            UPDATE 
              academic_calendar
            SET 
                archive=1,archived_by=1,archived_date=now()
            WHERE academic_year_id= 1;
        ";

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
              ':archive' => 1,
              ':archived_by' => $archived_by,
              ':archived_date' => date("Y-m-d H:i:s"),
              ':plan_id' =>$plan_id
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        } 
    }
}
?>