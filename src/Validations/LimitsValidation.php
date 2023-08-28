<?php
namespace Src\Validations;

class LimitsValidation {

    public static function rebDistribution($input)
    {
        if (empty($input['academic_year_id'])) {
            return false;
        }
        if (empty($input['minecofin_limit_id'])) {
            return false;
        }
        if (empty($input['district_code'])) {
            return false;
        }
        if (empty($input['qualification_id'])) {
            return false;
        }
        if (!isset($input['limits']) || $input['limits'] < 0) {
            return false;
        }
        if (empty($input['distribution_comment'])) {
            return false;
        }
        return true;
    }
    public static function distDistribution($input)
    {
        if (empty($input['post_request_id'])) {
            return false;
        }
           if (!isset($input['dde_post_distribution']) || $input['dde_post_distribution'] < 0) {
            return false;
        }
        if (empty($input['academic_year_id'])) {
            return false;
        }
        if (empty($input['district_code'])) {
            return false;
        }
        if (empty($input['qualification_id'])) {
            return false;
        }
        if (empty($input['position_code'])) {
            return false;
        }
        if (empty($input['dde_distribution_comment'])) {
            return false;
        }
        return true;
    }            
}