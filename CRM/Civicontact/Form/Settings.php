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
  public static $supportedFields = array(
    "Organization" => array(
      "sic_code",
      "organization_name",
      "legal_name",
      "legal_identifier",
    ),
    "Contact" => array(
      "address_name",
      "url",
      "supplemental_address_3",
      "supplemental_address_2",
      "supplemental_address_1",
      "street_address",
      "state_province",
      "postal_code",
      "county",
      "country",
      "city",
    ),
    "Household" => array(
      "household_name",
    ),
    "Individual" => array(
      "middle_name",
      "job_title",
      "suffix_id",
      "prefix_id",
      "gender_id",
      "formal_title",
      "current_employer",
      "birth_date",
    ),
  );

  public static $supportedCustomFieldDataTypes = array(
      "String[-]Text",
      "Money[-]Text",
      "String[-]Select",
      "String[-]Radio",
      "String[-]CheckBox",
      "String[-]Multi-Select",
      "Int[-]Text",
      "Int[-]Radio",
      "Memo[-]TextArea",
      "Date[-]Select Date",
      "Boolean[-]Radio",
      "StateProvince[-]Select State/Province",
      "StateProvince[-]Multi-Select State/Province",
      "Country[-]Select Country",
      "Country[-]Multi-Select Country",
      "Link[-]Link",
  );

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  public function getFormSettings() {
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
          $this->$add($setting['html_type'], $name, ts($setting['title']), CRM_Utils_Array::value('html_attributes', $setting, array()));
        }
        elseif (isset($setting['html_type']) && $setting['html_type'] == 'Select') {
          $optionValues = array();
          if (!empty($setting['pseudoconstant']) && !empty($setting['pseudoconstant']['optionGroupName'])) {
            $optionValues = CRM_Core_OptionGroup::values($setting['pseudoconstant']['optionGroupName'], FALSE, FALSE, FALSE, NULL, 'name');
          }
          else {
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
    $this->checkSelectedProfile();
    parent::buildQuickForm();
  }

  public function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    parent::postProcess();
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/cca/settings'));
  }

  /**
   *  Check selected Contact profile.
   */
  public function checkSelectedProfile() {
    $selectedProfile = Civi::settings()->get('cca_profile');
    if ($selectedProfile) {
      $supportFieldNames = getCCASupportedProfileFields();
      $selectedProfileFields = civicrm_api3("UFField", "get", array(
        'uf_group_id' => $selectedProfile,
        'sequential'  => TRUE,
        'is_active'   => TRUE,
        'field_name'  => array('NOT IN' => $supportFieldNames),
      ));

      $unSupportedFields = array();
      $customFields = array();
      foreach ($selectedProfileFields["values"] as $selectedProfileField) {
        if (isProfileFieldCustom($selectedProfileField["field_name"])) {
          $selectedProfileField["custom_field_id"] = CRM_Core_BAO_CustomField::getKeyID($selectedProfileField["field_name"]);
          $customFields[] = $selectedProfileField;
        } else {
          $unSupportedFields[] = $selectedProfileField;
        }
      }

      $customFieldIds = array_column($customFields, "custom_field_id");

      if (count($customFieldIds)) {
        $customFieldsToCheck = civicrm_api3("CustomField", "get", array(
          'id' => array('IN' => $customFieldIds),
          'sequential' => TRUE,
        ));

        $customFieldsToCheck = $customFieldsToCheck["values"];

        foreach ($customFieldsToCheck as $index => $customFieldToCheck) {
          if(!isCustomFieldSupported($customFieldToCheck)) {
              $customFieldToCheck["field_type"] = "Contact";
              $unSupportedFields[] = $customFieldToCheck;
          }
        }
      }

      if (count($unSupportedFields)) {
        $this->assign("profile_warning", "CiviContact does not support following fields from selected profile and it will not display them on add/edit contact page in application.");
        $this->assign("notsupported_profile_fields", $unSupportedFields);
      }
    }
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
          "description" => $this->_settings[$element->getName()]["description"],
        );
      }
    }
    return $elementNames;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   */
  public function saveSettings() {
    $settings = $this->getFormSettings();
    $cca_licence_code = Civi::settings()->get('cca_licence_code');
    if ($cca_licence_code != $this->_submittedValues["cca_licence_code"]) {
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
  public function setDefaultValues() {
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
    foreach ($activityTypes["values"] as $activityType) {
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
    foreach ($activityTypes["values"] as $activityType) {
      $activityTypeOptions[$activityType["value"]]  = $activityType["label"];
    }
    return $activityTypeOptions;
  }

  /**
   * Get all user defined profiles.
   *
   * @return array
   */
  public static function getUFGroups() {
    $ufGroups = civicrm_api3('UFGroup', 'get', array(
        'sequential' => 1,
        'is_active' => 1,
    ));
    $ufGroupOptions = array("0" => "- None -");
    foreach ($ufGroups["values"] as $ufGroup) {
      if (!isset($ufGroup["is_reserved"]) || (isset($ufGroup["is_reserved"]) && !$ufGroup["is_reserved"])) {
        $ufGroupOptions[$ufGroup["id"]]  = $ufGroup["title"];
      }
    }
    return $ufGroupOptions;
  }

}
