<?php
namespace Src\Models;

use Error;

class LivemoodleModel
{
    private $db = null;
    private $moodleDb = null;

    public function __construct($db, $moodleDb = null)
    {
        $this->db = $db;
        $this->moodleDb = $moodleDb;
    }

    public function get_enrollments($courseId)
    {
        $statement = "
            SELECT COUNT(u.id) as enrolled_students
            FROM  mdl_user u
            JOIN mdl_user_enrolments ue ON ue.userid = u.id
            JOIN mdl_enrol e ON e.id = ue.enrolid
            JOIN mdl_course c ON c.id = e.courseid
            JOIN mdl_context ct ON ct.instanceid = c.id AND ct.contextlevel = 50
            JOIN mdl_role_assignments ra ON ra.contextid = ct.id AND ra.userid = u.id
            JOIN mdl_role r ON r.id = ra.roleid
            WHERE c.id = 2 AND r.shortname = 'student';
        ";
        try {
            $statement = $this->moodleDb->query($statement);
            $statement->execute(array($courseId));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result[0];
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        } 
    }
}
