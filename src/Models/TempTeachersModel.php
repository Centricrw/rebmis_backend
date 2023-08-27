<?php
namespace Src\Models;

class TempTeachersModel
{

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function insert($data)
    {
        $statement = "
        INSERT
          INTO tbl_24
            (staff_code, staff_names, gender, date_of_birth, marital_status, nid, email_address, phone_number, social_security_number, type, staff_position, education_domain, education_sub_dommain, major, graduation_date, considered_qualification, highest_qualification, start_date_in_education, start_date_in_the_school, contract_type, bank_account, nationality, province_code, district_code, sector_code, cell_code, village_id, village, code, user_id)
          VALUES
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute($data);
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}
