<?php

use CRM_Requiredfields_ExtensionUtil as E;

class CRM_Requiredfields_Util
{
    public static function getSettings($param) {

        $settings = [];
        
        $sql = "SELECT * FROM civicrm_required_fields_settings";
        $result = CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

        while ($result->fetch()) {
            // Store each setting in the associative array.
            $settings[$result->param_name] = $result->param_value;
        }
    
        // Return the value of the specified setting, or null if it's not found.
        return $settings[$param] ?? null;
    }

}