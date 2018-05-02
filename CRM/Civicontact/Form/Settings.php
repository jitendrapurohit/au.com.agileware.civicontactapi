<?php

use CRM_Civicontact_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Civicontact_Form_Settings extends CRM_Core_Form {
  private $_settingFilter = array('group' => 'cca');
  private $_submittedValues = array();
  private $_settings = array();
  private $isSSLEnabled = FALSE;

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function getFormSettings() {
    if (empty($this->_settings)) {
      $settings = civicrm_api3('setting', 'getfields', array('filters' => $this->_settingFilter));
      $settings = $settings['values'];
      $this->_settings = $settings;
    }
    return $this->_settings;
  }

  public function buildQuickForm() {
    $licence_activated = Civi::settings()->get('cca_licence_activated');
    $settings = $this->getFormSettings();
    CRM_Utils_System::setTitle(ts('Settings - CiviContact'));
    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        $add = 'add' . $setting['quick_form_type'];
        if ($add == 'addElement') {
          $this->$add($setting['html_type'], $name, ts($setting['title']), CRM_Utils_Array::value('html_attributes', $setting, array ()));
        }
        elseif (isset($setting['html_type']) && $setting['html_type'] == 'Select') {
          $optionValues = array();
          if (!empty($setting['pseudoconstant']) && !empty($setting['pseudoconstant']['optionGroupName'])) {
            $optionValues = CRM_Core_OptionGroup::values($setting['pseudoconstant']['optionGroupName'], FALSE, FALSE, FALSE, NULL, 'name');
          } else {
            $optionValues = civicrm_api3('Setting', 'getoptions', array(
              'field' => $name,
            ));
            $optionValues = $optionValues["values"];
          }
          $this->add('select', $setting['name'], $setting['title'], $optionValues, FALSE, $setting['html_attributes']);
        }
        else {
          $this->$add($name, ts($setting['title']));
        }
        $this->assign("{$setting['description']}_description", ts('description'));
      }
    }
    
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->assign('licenceActivated', $licence_activated);
    $this->isSSLEnabled = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
    parent::buildQuickForm();
  }

  public function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    parent::postProcess();
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/cca/settings'));
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label) && $element->getName() != "cca_licence_activated" && (($element->getName() == "cca_force_ssl" && $this->isSSLEnabled) || $element->getName() != "cca_force_ssl")) {
        $elementNames[] = array(
          "name"        => $element->getName(),
          "description" => $this->_settings[$element->getName()]["description"]
        );
      }
    }
    return $elementNames;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function saveSettings() {
    $settings = $this->getFormSettings();
    $cca_licence_code = Civi::settings()->get('cca_licence_code');
    if($cca_licence_code != $this->_submittedValues["cca_licence_code"]) {
      Civi::settings()->set('cca_licence_activated', 0);
    }
    $values = array_intersect_key($this->_submittedValues, $settings);
    unset($values["cca_licence_activated"]);
    civicrm_api3('setting', 'create', $values);
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  function setDefaultValues() {
    $existing = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
    $defaults = array();
    $domainID = CRM_Core_Config::domainID();
    foreach ($existing['values'][$domainID] as $name => $value) {
      $defaults[$name] = $value;
    }
    return $defaults;
  }

  /**
   * Get the sync interval options to use in this form.
   *
   * @return array
   */
  public static function getSyncIntervalOptions() {
    return array(
      '900' => ts('15 minutes'),
      '1800' => ts('30 minutes'),
      '3600' => ts('Every hour'),
      '14400' => ts('Every 4 hours'),
      '86400' => ts('Daily'),
      'never' => ts('Never'),
    );
  }

  /**
   * Get the contact tile click actions availble.
   *
   * @return array
   */
  public static function getContactTileClickActions() {
    $activityTypes = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'return' => array("label", "value", "name"),
      'option_group_id' => "activity_type",
      'component_id' => array('IS NULL' => 1),
      'is_active' => 1,
    ));
    $clickActions = array();
    foreach($activityTypes["values"] as $activityType) {
      $clickActions["activity__{$activityType["value"]}__{$activityType["name"]}"]  = "Create {$activityType["label"]}";
    }
    return $clickActions;
  }

  /**
   * Get the contact tile click actions availble.
   *
   * @return array
   */
  public static function getActivityTypes() {
    $activityTypes = civicrm_api3('OptionValue', 'get', array(
        'sequential' => 1,
        'return' => array("label", "value", "name"),
        'option_group_id' => "activity_type",
        'component_id' => array('IS NULL' => 1),
        'is_active' => 1,
    ));
    $activityTypeOptions = array();
    foreach($activityTypes["values"] as $activityType) {
        $activityTypeOptions[$activityType["value"]]  = $activityType["label"];
    }
    return $activityTypeOptions;
 }

}
