<?php
use CRM_Civicontactsapp_ExtensionUtil as E;

/**
 * CCAGroupContactsLog.getmodifiedcontacts API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_c_c_a_group_contacts_log_getmodifiedcontacts_spec(&$spec) {
  $spec['createdat'] = array(
    'api.required' => 0,
    'title' => 'Created At',
    'type' => CRM_Utils_Type::T_TIMESTAMP,
  );
}

/**
 * CCAGroupContactsLog.create API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_c_c_a_group_contacts_log_create_spec(&$spec) {
  $spec['contactid'] = array(
    'api.required' => 1,
    'title' => 'Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'FKClassName' => 'CRM_Contact_DAO_Contact',
    'FKApiName' => 'Contact',
  );
  $spec['groupid'] = array(
    'api.required' => 1,
    'title' => 'Group ID',
    'type' => CRM_Utils_Type::T_INT,
    'FKClassName' => 'CRM_Contact_DAO_Group',
    'FKApiName' => 'Group',
  );
  $spec['action'] = array(
    'api.required' => 1,
    'title' => 'Action',
    'type' => CRM_Utils_Type::T_STRING,
  );
}

/**
 * CCAGroupContactsLog.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_c_c_a_group_contacts_log_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * CCAGroupContactsLog.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_c_c_a_group_contacts_log_getmodifiedcontacts($params) {
  if(isset($params["createdat"]) && isset($params["createdat"][">="]) && $params["createdat"][">="] != "") {
    $groupcontactlogs = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
    $contactsfound = array();
    foreach($groupcontactlogs["values"] as $groupcontactlog) {
      if(!in_array($groupcontactlog["contactid"], $contactsfound)) {
        $contactsfound[$groupcontactlog["contactid"]] = $groupcontactlog["action"];
      }
    }
    $contactids = array_keys($contactsfound);
    $contacts = getContacts(TRUE, $contactids);
    return tagContacts($contacts, $contactsfound);
  }

  $contacts = getContacts();
  return tagContacts($contacts);
}

function tagContacts($contacts, $actions = array()) {
  foreach($contacts["values"] as $index => $contact) {
    $action = "create";
    if(isset($actions[$contact["id"]])) {
      $action = $actions[$contact["id"]];
    }
    $contacts["values"][$index]["action"] = $action;
  }
  return $contacts;
}

function getContacts($bycontactids = FALSE, $contactids = array()) {

  $contactparams = array(
    'sequential'    => 1,
    'return'        => array("first_name","last_name","sort_name","image_URL","created_date","modified_date"),
    'api.Email.get' => array('return' => array("location_type_id", "email")),
    'api.Phone.get' => array('return' => array("phone_type_id", "phone")),
  );

  if(!$bycontactids) {
    $customfield_result = civicrm_api3('CustomField', 'getsingle', array(
      'sequential' => 1,
      'return' => array("id"),
      'name' => "Sync_to_CCA",
    ));
    $cca_sync_custom_id = $customfield_result["id"];
    $cca_sync_custom_key = "custom_".$cca_sync_custom_id;
    $group_params = array(
      'sequential' => 1,
      'return' => array("id"),
      $cca_sync_custom_key => 1,
    );
    $group_ids = civicrm_api3('Group', 'get', $group_params);
    $group_ids = array_column($group_ids["values"], 'id');
    if(count($group_ids) == 0) {
      $group_ids[] = "-1";
    }
    $contactparams["group"] = array('IN' => $group_ids);
  } else {
    if(count($contactids) == 0) {
      $contactids[] = "-1";
    }
    $contactparams["id"] = array('IN' => $contactids);
  }

  $contacts = civicrm_api3('Contact', 'get', $contactparams);

  return $contacts;
}
