<?php

use CRM_Requiredfields_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Requiredfields_Form_RequiredActivity extends CRM_Core_Form {

  /**
     * @throws \CRM_Core_Exception
     */
  public function buildQuickForm(): void {

    $sql = "SELECT * FROM civicrm_required_fields_settings ic";
    $result = CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

    $params = array();
    while ($result->fetch()) {
      $params[$result->param_name] = $result->param_value;
    }
    
    // Populate the setting with the saved settings from the db
    $defaults = array(
      'required_activity_id' => isset($params['required_activity_id']) ? $params['required_activity_id'] : '',
      'contact_id' => isset($params['contact_id']) ? $params['contact_id'] : '',
      'relationship_type_id' => isset($params['relationship_type_id']) ? $params['relationship_type_id'] : '',
      'required' => isset($params['required']) ? (bool) $params['required'] : false,
    );

    $this->setDefaults($defaults);


    $this->assign('activityOptions', $this->getActivityTypeOptions());
    $this->assign('relationshipOptions', $this->getRelationshipTypeOptions());
    $this->assign('contactOptions', $this->getContact());

    // add form elements
    $this->addEntityRef('contact_id', 'Select Contacts', [
      'entity' => 'Contact',
      'multiple' => TRUE,
      'select' => ['minimumInputLength' => 0],
      'placeholder' => ts('- select -'),
    ], FALSE);
    $this->add('select', 
    'required_activity_id', 
    'Choose the Activity', 
    $this->getActivityTypeOptions(), 
    TRUE, // Required or not
    ['multiple' => 'multiple', 'class' => 'crm-select2', 'placeholder' => ts('- select -')] // Placeholder attribute
    );
    $this->add(
      'select', // field type
      'relationship_type_id', // field name
      'Choose the Relationship Type', // field label
      $this->getRelationshipTypeOptions(), // list of options
      TRUE, // is required
      ['multiple' => 'multiple', 'class' => 'crm-select2', 'placeholder' => ts('- select -')] // Placeholder attribute
    );
    $this->addElement('checkbox',
     'required',
     'Is Assigned to Required?');
    $this->addButtons([
      [ 
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }


  public function postProcess(): void {

    $postedVals = array(
      'required_activity_id' => null,
      'contact_id' => null,
      'relationship_type_id' => null,
      'required' => null
    );

    $values = $this->exportValues();

    $values['required_activity_id'] = is_array($values['required_activity_id']) 
        ? implode(',', $values['required_activity_id']) 
        : (string)$values['required_activity_id'];

    $values['relationship_type_id'] = is_array($values['relationship_type_id']) 
        ? implode(',', $values['relationship_type_id']) 
        : (string)$values['relationship_type_id'];

    $postedVals['required_activity_id'] = $values['required_activity_id'];
    $postedVals['contact_id'] = $values['contact_id'];
    $postedVals['relationship_type_id'] = $values['relationship_type_id'];
    $postedVals['required'] = isset($values['required']) ? 1 : 0;


    $checkFields = [
      'required_activity_id' => 'Activity',
      'relationship_type_id' => 'Relationship Type',
    ];
    
    foreach ($postedVals as $key => $value) {
      if (in_array($key, array_keys($checkFields)) && $value == null) {
        CRM_Core_Session::setStatus("\"".$checkFields[$key]."\" field is required", ts('Empty field'), 'warning', array('expires' => 5000));
        return;
      }
    }

    $sql =  "TRUNCATE TABLE civicrm_required_fields_settings";
    CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

    foreach ($postedVals as $key => $value) {
      $sql =  "INSERT INTO civicrm_required_fields_settings(param_name, param_value) VALUES('$key', '$value')";
      CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
    }

    // Notify the user of success
    CRM_Core_Session::setStatus(E::ts('Your settings have been saved.'), '', 'success');  


    parent::postProcess();
  }

  // Get Option Values 
  public function getActivityTypeOptions(): array {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => "activity_type",
      'component_id' => "CiviCase",
    ]);

    $options = [];
    if (!empty($result['values'])) {
      foreach ($result['values'] as $value) {
        $options[$value['value']] = E::ts($value['label']);
      }
    return $options;
    }
  }

  public function getRelationshipTypeOptions(): array {
    $result = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'options' => ['limit' => 0],
    ]);

    $options = [];
    if (!empty($result['values'])) {
      foreach ($result['values'] as $value) {
        // Using label_a_b as the label for simplicity; adjust as needed
        $options[$value['id']] = E::ts($value['label_a_b']);
      }
      return $options;
    }
  }

  public function getContact() : array {
    $result = civicrm_api3('Contact', 'get', [
      'sequential' => 1,
    ]);
    
    $options = ['' => E::ts('- select -')];
    if (!empty($result['values'])) {
      foreach ($result['values'] as $value) {
        // Using label_a_b as the label for simplicity; adjust as needed
        $options[$value['id']] = E::ts($value['display_name']);
      }
      return $options;
    }
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames(): array {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
