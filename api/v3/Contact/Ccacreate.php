<?php
use CRM_Civicontact_ExtensionUtil as E;

/**
 * Contact.Ccacreate API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_contact_Ccacreate_spec(&$spec) {

}

/**
 * Contact.Ccacreate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contact_Ccacreate($params) {
  $createdContact = civicrm_api3('Contact', 'create', $params);
  $params["id"] = $createdContact["id"];
  _cca_save_profile_fields_of_contact($params);
  return $createdContact;
}

/**
 * Save profile fields data of a contact
 *
 * @param $params
 * @throws CiviCRM_API3_Exception
 */
function _cca_save_profile_fields_of_contact($params) {
  if(isset($params["ProfileFieldsData"]) && count($params["ProfileFieldsData"])) {
    $profileFieldsData = $params["ProfileFieldsData"];
    $profileFieldsParams = array();

    $selectedProfileFields = civicrm_api3('UFGroup', 'ccaprofilefields', array(
        'sequential' => 1,
    ));
    $profileGroupId = $selectedProfileFields["group_id"];

    $selectedProfileFields = $selectedProfileFields["values"];
    $profileFieldsArray = array();
    $profileFieldsParams["gid"] = $profileGroupId;
    foreach($selectedProfileFields as $selectedProfileField) {
        $profileFieldsArray[$selectedProfileField["name"]] = $selectedProfileField;
    }

    foreach($profileFieldsData as $profileFieldData) {
        $profileFieldsParams[$profileFieldData["fieldname"]] = $profileFieldData["value"];

        if(isset($profileFieldsArray[$profileFieldData["fieldname"]])) {
            $profielFieldToCheck = $profileFieldsArray[$profileFieldData["fieldname"]];

            // Setting checkbox values
            if ($profielFieldToCheck["html_type"] == 'CheckBox') {
                if ($profileFieldData["value"] != '') {
                    $checkboxValues =  explode(",", $profileFieldData["value"]);
                    $profileFieldsParams[$profileFieldData["fieldname"]] = array();

                    foreach($checkboxValues as $checkboxValue) {
                        $profileFieldsParams[$profileFieldData["fieldname"]][$checkboxValue] = 1;
                    }
                }
            }

            // Setting Multi-Select values
            if ($profielFieldToCheck["html_type"] == "Multi-Select State/Province" || $profielFieldToCheck["html_type"] == 'Multi-Select' || $profielFieldToCheck["html_type"] == 'Multi-Select Country') {
                $multiSelectValues = array();
                if ($profileFieldData["value"] != '') {
                    $multiSelectValues =  explode(",", $profileFieldData["value"]);
                }
                $profileFieldsParams[$profileFieldData["fieldname"]] = $multiSelectValues;
            }
        }
    }

    CRM_Contact_BAO_Contact::createProfileContact(
        $profileFieldsParams,
        $profileFieldsArray,
        $params["id"],
        NULL,
        $profileGroupId,
        NULL,
        TRUE
    );
  }
}