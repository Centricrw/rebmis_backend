<?php
namespace Src\Models;

use Error;

class SchoolLocationModal
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findSchoolLocation($location)
    {
        try {
            if (isset($location["province_code"]) && empty($location["district_code"])) {
                $statement = "SELECT * FROM `schools_conf` WHERE `province_code`=:province_code";
                $statement = $this->db->prepare($statement);
                $statement->execute(array(
                    ':province_code' => $location['province_code'],
                ));
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            } else if (isset($location["district_code"]) && empty($location["sector_code"])) {
                $statement = "SELECT * FROM `schools_conf` WHERE `district_code`=:district_code";
                $statement = $this->db->prepare($statement);
                $statement->execute(array(
                    ':district_code' => $location['district_code'],
                ));
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            } else if (isset($location["sector_code"]) && empty($location["cell_code"])) {
                $statement = "SELECT * FROM `schools_conf` WHERE `sector_code`=:sector_code";
                $statement = $this->db->prepare($statement);
                $statement->execute(array(
                    ':sector_code' => $location['sector_code'],
                ));
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            } else if (isset($location["cell_code"])) {
                $statement = "SELECT * FROM `schools_conf` WHERE `cell_code`=:cell_code";
                $statement = $this->db->prepare($statement);
                $statement->execute(array(
                    ':cell_code' => $location['cell_code'],
                ));
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $statement = "SELECT * FROM `schools_conf`";
                $statement = $this->db->prepare($statement);
                $statement->execute();
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

}
