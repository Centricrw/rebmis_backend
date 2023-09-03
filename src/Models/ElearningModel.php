<?php
namespace Src\Models;

use Error;

class ElearningModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function connectCourse($cohortId, $courseLink)
    {
        $statement = "UPDATE cohorts SET courseLink = :courseLink WHERE cohortId = :cohortId";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':cohortId' => $cohortId,
                ':courseLink' => $courseLink,
            ));
            return $data;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
