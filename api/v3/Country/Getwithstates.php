<?php
use CRM_Civicontact_ExtensionUtil as E;

/**
 * Country.Getwithstates API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_country_Getwithstates_spec(&$spec) {

}

/**
 * Country.Getwithstates API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_country_Getwithstates($params) {
  $countries = civicrm_api3("Country", "get", array(
    'options'    => array('limit' => 0, 'sort' => "name ASC"),
    'api.StateProvince.get' => array(
        'options'    => array('limit' => 0, 'sort' => "name ASC"),
        'sequential' => FALSE,
    ),
  ));

  foreach ($countries["values"] as $countryid => $country) {
    $states = $country["api.StateProvince.get"]["values"];
    foreach($states as $stateid => $state) {
      $counties = CRM_Core_PseudoConstant::countyForState($stateid);
      $countries["values"][$countryid]["api.StateProvince.get"]["values"][$stateid]["counties"] = $counties;
    }
  }

  return $countries;
}
