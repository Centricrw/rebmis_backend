<?php
namespace Src\Models;

use Error;

class TraineersModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // role_id
    // school_code
    // sector_code
    // district_code

    public function getTrainees($cohortId, $user_role_details)
    {
        $condition = "";
        $conditionArray = array(
            ':cohortId' => $cohortId,
            ':status' => "Active",
        );
        $role = $user_role_details['role_id'];

        if (isset($user_role_details['school_code']) && $role == "2") {
            $condition = "AND T.school_code = :school_code";
            $conditionArray = array(
                ':cohortId' => $cohortId,
                ':status' => "Active",
                ':school_code' => $user_role_details['school_code'],
            );
        } elseif (isset($user_role_details['sector_code']) && $role == "18") {
            $condition = "AND T.sector_code = :sector_code";
            $conditionArray = array(
                ':cohortId' => $cohortId,
                ':status' => "Active",
                ':sector_code' => $user_role_details['sector_code'],
            );
        } elseif (isset($user_role_details['district_code']) && ($role == "3" || $role == "7")) {
            $condition = "AND T.district_code = :district_code";
            $conditionArray = array(
                ':cohortId' => $cohortId,
                ':status' => "Active",
                ':district_code' => $user_role_details['district_code'],
            );
        } else {
            $condition = "";
            $conditionArray = array(
                ':cohortId' => $cohortId,
                ':status' => "Active",
            );
        }

        $statement = "SELECT T.*, S.school_name, SL.sector_name, SL.district_name, UR.role_id, UR.qualification_id, UR.position_code, UR.status, U.staff_code, U.email, U.nid, U.sex FROM trainees T
            INNER JOIN schools S ON S.school_code = T.school_code
            INNER JOIN school_location SL ON SL.village_id = S.region_code
            INNER JOIN user_to_role UR ON T.userId = UR.user_id
            INNER JOIN users U ON U.user_id = UR.user_id
            WHERE T.cohortId = :cohortId AND UR.status = :status $condition";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute($conditionArray);
            $teachers = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $teachers;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
