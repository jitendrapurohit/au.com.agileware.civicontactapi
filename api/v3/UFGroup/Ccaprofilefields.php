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
    if ($ccaprofile) {
      $selectedProfileFields = getCCASelectedProfileFields();
      if (!count($selectedProfileFields)) {
        throw new API_Exception('CCA Profile is selected but with zero supported fields.', 404);
      }
      _cca_api_modify_profile_fields($selectedProfileFields);
      return array(
        "is_error" => 0,
        "count"    => count($selectedProfileFields),
        "values"   => $selectedProfileFields,
      );
    } else {
      throw new API_Exception('CCA Profile is not selected.', 404);
    }
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
  }
}
