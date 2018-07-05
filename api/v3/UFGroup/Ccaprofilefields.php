<?php
use CRM_Civicontact_ExtensionUtil as E;

/**
 * UFGroup.Ccaprofilefields API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_uf_group_Ccaprofilefields_spec(&$spec) {

}

/**
 * UFGroup.Ccaprofilefields API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_uf_group_Ccaprofilefields($params) {
    $ccaprofile = getCCASelectedProfile();
    $ccaProfileId = Civi::settings()->get('cca_profile');
    if ($ccaprofile) {
      $selectedProfileFields = getCCASelectedProfileFields();
      if (!count($selectedProfileFields)) {
         return _cca_contacts_empty_profile_fields_response($ccaProfileId);
      }
      _cca_api_modify_profile_fields($selectedProfileFields);
      return array(
        "is_error" => 0,
        "group_id" => $ccaProfileId,
        "count"    => count($selectedProfileFields),
        "values"   => $selectedProfileFields,
      );
    } else {
      return _cca_contacts_empty_profile_fields_response($ccaProfileId);
    }
}

/**
 * Return empty profile fields reponse when profile is not selected or have no supported profile fields.
 *
 * @return array
 */
function _cca_contacts_empty_profile_fields_response($ccaProfileId) {
  return array(
    "is_error" => 0,
    "count"    => 0,
    "group_id" => $ccaProfileId,
    "values"   => array(),
  );
}

/**
 * Modify profile fields
 *  - Add options for selection fields.
 * @param $selectedProfileFields
 */
function _cca_api_modify_profile_fields(&$selectedProfileFields) {
  foreach ($selectedProfileFields as &$selectedProfileField) {
    if(in_array($selectedProfileField["name"], array('gender_id', 'prefix_id', 'suffix_id'))) {
      $options = array();
      $pseudoValues = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', $selectedProfileField["name"]);
      foreach ($pseudoValues as $key => $var) {
        $options[] = array(
          "key" => $key,
          "val" => $var,
        );
      }
      $selectedProfileField["selectionvalues"] = $options;
    }
    elseif(isProfileFieldCustom($selectedProfileField["name"]))  {
      $field = CRM_Core_BAO_CustomField::getFieldObject(CRM_Core_BAO_CustomField::getKeyID($selectedProfileField["name"]));
      $isSelect = (in_array($selectedProfileField["html_type"], array(
        'Select',
        'Multi-Select',
        'CheckBox',
        'Autocomplete-Select',
        'Radio',
      )));
      if ($isSelect) {
        $options = $field->getOptions('create');
        $optionsToSend = array();
        foreach($options as $key => $option) {
          $optionsToSend[] = array(
            "key" => $key,
            "val" => $option,
          );
        }
        $selectedProfileField["selectionvalues"] = $optionsToSend;
      }
    }
  }
}
