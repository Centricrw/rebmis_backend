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
        $statement = " SELECT COUNT(u.id) as enrolled_students
            FROM  mdl_user u
            JOIN mdl_user_enrolments ue ON ue.userid = u.id
            JOIN mdl_enrol e ON e.id = ue.enrolid
            JOIN mdl_course c ON c.id = e.courseid
            JOIN mdl_context ct ON ct.instanceid = c.id AND ct.contextlevel = 50
            JOIN mdl_role_assignments ra ON ra.contextid = ct.id AND ra.userid = u.id
            JOIN mdl_role r ON r.id = ra.roleid
            WHERE c.id = ? AND r.shortname = 'student';
        ";
        try {
            $statement = $this->moodleDb->prepare($statement);
            $statement->execute(array($courseId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results[0];
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        } 
    }

    public function get_pa_grades($courseId){
        $statement = "
            SELECT c.id as Course_Id, c.shortname AS shortname, c.fullname AS fullname, u.id AS User_Id,
            u.firstname AS firstname, u.lastname AS lastname, u.email AS email, COALESCE(ROUND(gg.finalgrade,2),0) as finalgrade

            FROM mdl_user u
            INNER JOIN mdl_role_assignments ra ON ra.userid = u.id
            INNER JOIN mdl_context ct ON ct.id = ra.contextid
            INNER JOIN mdl_course c ON c.id = ct.instanceid
            INNER JOIN mdl_role r ON r.id = ra.roleid
            LEFT JOIN (SELECT
                u.id AS userid,c.id as courseid,
                g.finalgrade AS finalgrade
                FROM mdl_user u
                JOIN mdl_grade_grades g ON g.userid = u.id
                JOIN mdl_grade_items gi ON g.itemid =  gi.id
                JOIN mdl_course c ON c.id = gi.courseid 
                where gi.itemtype = 'course') gg ON gg.userid = u.id and gg.courseid = c.id 

            WHERE c.id = ? ORDER BY finalgrade DESC
        ";
        try {
            $statement = $this->moodleDb->prepare($statement);
            $statement->execute(array($courseId));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }

    public function get_course_grades($previous_courseid,$current_courseid){
        $statement = "
            SELECT
                u.id AS User_Id,
                u.firstname AS firstname,
                u.lastname AS lastname,
                u.email AS email,
                COALESCE(ROUND(g1.finalgrade, 2), 0) AS grades_course_1,
                COALESCE(ROUND(g2.finalgrade, 2), 0) AS grades_course_2,
                COALESCE(ROUND(p2.progress, 2), 0) AS progress_course_2
            FROM
                mdl_user u
            LEFT JOIN (
                SELECT
                    gg.userid,
                    gg.finalgrade
                FROM
                    mdl_grade_grades gg
                JOIN mdl_grade_items gi ON gg.itemid = gi.id
                WHERE
                    gi.courseid = $previous_courseid AND gi.itemtype = 'course'
            ) g1 ON g1.userid = u.id
            LEFT JOIN (
                SELECT
                    gg.userid,
                    gg.finalgrade
                FROM
                    mdl_grade_grades gg
                JOIN mdl_grade_items gi ON gg.itemid = gi.id
                WHERE
                    gi.courseid = $current_courseid AND gi.itemtype = 'course'
            ) g2 ON g2.userid = u.id
            LEFT JOIN (
                SELECT
                    ccc.userid,
                    SUM(ccc.course) AS progress
                FROM
                    mdl_course_completion_crit_compl ccc
                WHERE
                    ccc.course = $current_courseid
                GROUP BY
                    ccc.userid
            ) p2 ON p2.userid = u.id
            INNER JOIN mdl_role_assignments ra ON ra.userid = u.id
            INNER JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
            INNER JOIN mdl_course c ON c.id = ct.instanceid AND c.id IN (2, 3)
            INNER JOIN mdl_role r ON r.id = ra.roleid
            WHERE
                r.shortname = 'student'
            ORDER BY
                u.lastname, u.firstname;
        ";
        try {
            $statement = $this->moodleDb->prepare($statement);
            $statement->execute();
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $results;
        } catch (\PDOException $e) {
            throw new Error($e->getMessage());
        }
    }
}
