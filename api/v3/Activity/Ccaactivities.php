<?php
use CRM_Civicontact_ExtensionUtil as E;

/**
 * Activity.Ccaactivities API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_activity_Ccaactivities_spec(&$spec) {

}

/**
 * Activity.Ccaactivities API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_activity_Ccaactivities($params) {
  $contactsToFetch = $result = civicrm_api3('CCAGroupContactsLog', 'getmodifiedcontacts', array(
      'sequential' => 1,
      'return'     => array('contact_id'),
  ));
  $contactsToFetch = array_column($contactsToFetch["values"], "contact_id");
  $contactActivities = Civi::settings()->get('cca_activity_types');
  $contactActivities = mb_split("\W", $contactActivities);

  unset($contactActivities[0]);
  unset($contactActivities[count($contactActivities)]);

  $params['contact_id'] = $contactsToFetch;
  $params['activity_type_id'] = $contactActivities;

  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
