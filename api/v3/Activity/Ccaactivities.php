<?php

use CRM_Civicontact_ExtensionUtil as E;

/**
 * Activity.Ccaactivities API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_activity_Ccaactivities_spec(&$spec) {
  $spec['createdat'] = [
    'api.required' => 0,
    'title' => 'Created At',
    'type' => CRM_Utils_Type::T_TIMESTAMP,
  ];
}

/**
 * Activity.Ccaactivities API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws API_Exception
 * @see civicrm_api3_create_error
 * @see civicrm_api3_create_success
 */
function civicrm_api3_activity_Ccaactivities($params) {
  $params['sequential'] = 1;
  $contactsToFetch = $result = civicrm_api3('CCAGroupContactsLog', 'getmodifiedcontacts', [
    'sequential' => 1,
    'return' => ['contact_id'],
  ]);
  $contactsToFetch = array_column($contactsToFetch["values"], "contact_id");
  $contactActivities = Civi::settings()->get('cca_activity_types');
  $contactActivities = mb_split("\W", $contactActivities);

  unset($contactActivities[0]);
  unset($contactActivities[count($contactActivities)]);

  if (count($contactsToFetch) == 0) {
    $contactsToFetch[] = "";
  }
  if (count($contactActivities) == 0) {
    $contactActivities[] = "";
  }

  $params['contact_id'] = ["IN" => $contactsToFetch];
  $params['activity_type_id'] = ["IN" => $contactActivities];

  $params['options'] = [
    'limit' => '0',
  ];

  $params['return'] = [
    "activity_type_id",
    "subject",
    "details",
    "activity_date_time",
    "source_contact_id",
    "target_contact_id",
    "assignee_contact_id",
    "created_date",
    "modified_date",
    "id",
  ];

  if (isset($params["createdat"])) {
    $params['created_date'] = $params['createdat'];
    $params['modified_date'] = $params['createdat'];
    $params['options']['or'] = [
      [
        'created_date',
        'modified_date',
      ],
    ];
  }

  $updatedResult = civicrm_api3_activity_get($params);

  if (isset($params["createdat"])) {
    $updatedContacts = civicrm_api3('CCAGroupContactsLog', 'getmodifiedcontacts', [
      'sequential' => 1,
      'return' => ['contact_id'],
      'createdat' => $params["createdat"],
    ]);

    $updatedContacts = array_column($updatedContacts["values"], "contact_id");
    if (count($updatedContacts)) {
      unset($params['created_date']);
      unset($params['modified_date']);
      unset($params['createdat']);
      unset($params['options']['or']);
      $params['contact_id'] = ["IN" => $updatedContacts];
      $updatedContactsResult = civicrm_api3_activity_get($params);
      _civicontact_merge_activities_result($updatedResult, $updatedContactsResult);
    }
  }

  $updatedResult['count'] = count($updatedResult['values']);

  return $updatedResult;
}

/**
 * Function to merge two activitiy APIs result.
 *
 * @param $updatedResult
 * @param $updatedContactsResult
 *
 */
function _civicontact_merge_activities_result(&$updatedResult, $updatedContactsResult) {
  $activitiesInList = [];
  foreach ($updatedResult['values'] as $activity) {
    $activitiesInList[] = $activity["id"];
  }

  foreach ($updatedContactsResult['values'] as $activity) {
    if (!in_array($activity['id'], $activitiesInList)) {
      $updatedResult['values'][] = $activity;
    }
  }
}
