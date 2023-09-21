<?php
namespace Src\Models;

use Error;

class CohortsModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllCohorts($trainingId)
    {
        $statement = "SELECT C.cohortId, C.courseLink, C.cohortName, C.cohortStart, C.cohortEnd, C.trainingId FROM cohorts C
        WHERE C.trainingId = ?";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($trainingId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $newResult = [];
            foreach ($results as $result) {
                array_push($newResult, $result);
            }

            return $newResult;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function addACohort($data, $user_id, $trainingId)
    {
        $statement = "INSERT INTO cohorts (cohortName,cohortStart,cohortEnd,trainingId,createdBy)
      VALUES(:cohortName,:cohortStart,:cohortEnd,:trainingId,:createdBy)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cohortName' => $data['cohortName'],
                ':cohortStart' => $data['cohortStart'],
                ':cohortEnd' => $data['cohortEnd'],
                ':trainingId' => $trainingId,
                ':createdBy' => $user_id,
            ));
            $data['cohortId'] = $this->db->lastInsertId();
            $data['requestedTrainees'] = '0';
            $data['providedTrainees'] = '0';
            return $data;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
