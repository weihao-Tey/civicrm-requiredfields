<?php
require_once 'requiredfields.civix.php';

use CRM_Requiredfields_ExtensionUtil as E;


/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function requiredfields_civicrm_config(&$config)
{
  _requiredfields_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function requiredfields_civicrm_install()
{
  _requiredfields_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function requiredfields_civicrm_enable()
{
  _requiredfields_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_buildForm().
 *
 *  MAKE A FIELD IN THE CASE ACTIVITY FORM REQUIRED
 * 
 * @param string $formName
 *   The name of the form.
 * @param CRM_Core_Form $form
 *   The form object.
 */
function requiredfields_civicrm_buildForm($formName, &$form) {

  // Check that the form is a Case Activity Form
  if ($formName === 'CRM_Case_Form_Activity') {

    $caseid = implode(",", $form->getVar('_caseId'));


    $settings = [];
        
    $sql = "SELECT * FROM civicrm_required_fields_settings";
    $result = CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

    while ($result->fetch()) {
        // Store each setting in the associative array.
        $settings[$result->param_name] = $result->param_value;
    }

    // GET ACTIVITY ID FROM THE SETTINGS 
    $settings_ActivityID = $settings['required_activity_id'];
    // CONVERT TO ARRAY
    $activityIDArray = explode(',', $settings_ActivityID);
    // Convert all elements to integers
    $activityIDArray = array_map('intval', $activityIDArray);

    $settingArray_RelationshipType = $settings['relationship_type_id'];
    $relationshipTypeID = explode(',', $settingArray_RelationshipType);
    $relationshipTypeID = array_map('intval', $relationshipTypeID);

    // GET CONTACTS IF ANY TO SET AS DEFAULT VALUES
    $settings_ContactID = $settings['contact_id'];
    // CONVERT TO ARRAY
    $settingContactIDArray = explode(',', $settings_ContactID); 

    $relationships = civicrm_api4('Relationship', 'get', [
      'select' => [
        'case_id',
        'contact_id_a',
        'contact_id_b',
      ],
      'where' => [
        ['case_id', '=', $caseid],
        ['relationship_type_id', 'IN', $relationshipTypeID],
        ['is_active', '=', TRUE],
      ],
      'checkPermissions' => FALSE,
    ]);
    
    
    $relationshipArray = [];
    foreach ($relationships as $relationships){

      $relationshipArray[] = $relationships['contact_id_b'];

    }

    $mergeArray = array_merge($settingContactIDArray, $relationshipArray);
    // Make sure $mergeArray is an int
    $settingArray = array_map('intval', $mergeArray);

    $unique = array_unique($settingArray);

    $activityTypeId = NULL; // Initialize the variable

    if (!empty($form->getVar('_activityTypeId'))){
      // get the Activity Type ID of the current Form 
      $activityTypeId = $form->getVar('_activityTypeId');
    }

    // CHECK IF ACTIVITY ID IS IN ARRAY 
    if (in_array($activityTypeId, $activityIDArray)){
      if(isset($settings['required'])){
        if($settings['required'] == TRUE){
        // Make the "Assigned To" field required
        $form->addRule('assignee_contact_id', ts('Assigned To is a required field.'), 'required');
        }
      }

      // Set default value for the "Assigned To" field 
      $form->setDefaults(array('assignee_contact_id' => $unique));
    }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function requiredfields_civicrm_navigationMenu(&$menu) {
  _requiredfields_civix_insert_navigation_menu($menu, 'Administer/System Settings', array(
    'label' => ts('RequiredFields'),
    'name' => 'required_fields',
    'url' => 'civicrm/requiredactivity?reset=1',
    'permission' => 'access CiviCRM, administer CiviCRM',
    'operator' => 'AND',
    'separator' => 0,
  ));
  _requiredfields_civix_navigationMenu($menu);
}



