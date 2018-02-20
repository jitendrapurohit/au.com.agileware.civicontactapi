<?php
use CRM_Civicontact_ExtensionUtil as E;

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

    $isCiviTeamsInstalled = isCiviTeamsExtensionInstalled();

    $groupslogparams = array(
      'sequential'    =>  1,
      'return'        => array("groupid", "action", "groupid.title"),
      'createdat'     => $params["createdat"],
      'options'       => array('sort' => "id DESC"),
    );

    $uniqueGroups = array();
    $logGroupsAdded = array();

    if($isCiviTeamsInstalled) {
        $teamgroupsToCheck = array();
        $teamGroupsToDelete = array();

        $teams = getContactTeams();
        $teamsToRemove = array();
        $modifiedTeams = getModifiedTeams($params["createdat"]);
        $teamsToAdd = array();

        foreach($modifiedTeams["values"] as $modifiedTeam) {
            if(!$modifiedTeam["status"]) {
                $teamsToRemove[] = $modifiedTeam["team_id"];
            }

            if($modifiedTeam["status"]) {
                $teamsToAdd[] = $modifiedTeam["team_id"];
            }
        }

        $teamgroups = getTeamGroups($teams, FALSE);
        $teamgroups = $teamgroups["values"];

        $modifiedteamgroups = getTeamGroups($teams, FALSE, $params["createdat"]);
        $modifiedteamgroups = $modifiedteamgroups["values"];

        $teamgroupsToRemove = getTeamGroups($teamsToRemove, FALSE);
        $teamgroupsToRemove = $teamgroupsToRemove["values"];

        $teamGroupsToAdd = getTeamGroups($teamsToAdd, TRUE);
        $teamGroupsToAdd = $teamGroupsToAdd["values"];

        if(count($modifiedteamgroups) > 0) {
            foreach($modifiedteamgroups as $index => $teamgroup) {
                if($teamgroup["isactive"]) {
                    $teamgroupsToCheck[] = $teamgroup["entity_id"];
                    $modifiedteamgroups[$index]["isactive"] = "on";
                } else {
                    $teamGroupsToDelete[] = $teamgroup["entity_id"];
                    $modifiedteamgroups[$index]["isactive"] = "off";
                }
            }

            $activeGroups = getCCAActiveGroups($teamgroupsToCheck, true);
            foreach($activeGroups as $activeGroup) {
                if(!array_key_exists($activeGroup["id"], $logGroupsAdded)) {
                    $uniqueGroups[$activeGroup["id"]] = array(
                        "action"      => "on",
                        "groupid"     => $activeGroup["id"],
                        "groupname"   => $activeGroup["title"],
                    );
                }
            }

            $groupDetails = getGroupDetailsByIds($teamGroupsToDelete);
            foreach($groupDetails as $groupDetail) {
                if(!array_key_exists($groupDetail["id"], $logGroupsAdded)) {
                    $uniqueGroups[$groupDetail["id"]] = array(
                        "action"      => "off",
                        "groupid"     => $groupDetail["id"],
                        "groupname"   => $groupDetail["title"],
                    );
                }
            }

            $uniqueGroupsClone = $uniqueGroups;
            $uniqueGroups = array();

            foreach($modifiedteamgroups as $teamgroup) {
                if(array_key_exists($teamgroup["entity_id"], $uniqueGroupsClone)) {
                    $uniqueGroups[] = $uniqueGroupsClone[$teamgroup["entity_id"]];
                }
            }
        }

        $teamgroupsToCheck = array();
        foreach($teamgroups as $teamgroup) {
            if($teamgroup["isactive"]) {
                $teamgroupsToCheck[] = $teamgroup["entity_id"];
                $modifiedteamgroups[$index]["isactive"] = "on";
            }
        }

        if(count($teamgroupsToCheck) == 0) {
            $teamgroupsToCheck[] = "-1";
        }

        $groupslogparams["groupid"] = array("IN" => $teamgroupsToCheck);

        if(count($teamgroupsToRemove) > 0) {
            $teamgroupsToRemove = array_column($teamgroupsToRemove , "entity_id");
            $groupDetails = getGroupDetailsByIds($teamgroupsToRemove);
            foreach($groupDetails as $groupDetail) {
                if(!array_key_exists($groupDetail["id"], $logGroupsAdded)) {
                    $uniqueGroups[$groupDetail["id"]] = array(
                        "action"      => "off",
                        "groupid"     => $groupDetail["id"],
                        "groupname"   => $groupDetail["title"],
                    );
                }
            }
        }

        if(count($teamGroupsToAdd) > 0) {
            $teamGroupsToAdd = array_column($teamGroupsToAdd , "entity_id");
            $groupDetails = getGroupDetailsByIds($teamGroupsToAdd);
            foreach($groupDetails as $groupDetail) {
                if(!array_key_exists($groupDetail["id"], $logGroupsAdded)) {
                    $uniqueGroups[$groupDetail["id"]] = array(
                        "action"      => "on",
                        "groupid"     => $groupDetail["id"],
                        "groupname"   => $groupDetail["title"],
                    );
                }
            }
        }
    }

    $groupslog = civicrm_api3('CCAGroupsLog', 'get', $groupslogparams);

    foreach($groupslog["values"] as $grouplog) {
      if(!array_key_exists($grouplog["groupid"], $logGroupsAdded)) {
        $uniqueGroups[] = array(
          "action"      => $grouplog["action"],
          "groupid"     => $grouplog["groupid"],
          "groupname"   => $grouplog["groupid.title"],
        );
      }
    }

    $contactsfound = array();
    foreach($uniqueGroups as $index => $groupinfo) {
      $contactids = getGroupContacts($groupinfo["groupname"]);
      $contactactions = array_fill_keys($contactids, ($groupinfo["action"] == "on") ? "create" : "delete");
      $contactsfound = $contactsfound + $contactactions;
    }

    $groupcontactlogs = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
    foreach($groupcontactlogs["values"] as $groupcontactlog) {
      if(!array_key_exists($groupcontactlog["contactid"], $contactsfound)) {
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

function getGroupContacts($groupname) {
  $contactids = civicrm_api3('Contact', 'get', array(
    "group"      => $groupname,
    "return"     => array("id"),
    "sequential" => 1,
    "options"    => array("limit" => -1)
  ));

  $contactids = array_column($contactids["values"], "id");
  return $contactids;
}

function tagContacts($contacts, $actions = array()) {
  foreach($contacts["values"] as $index => $contact) {
    $action = "create";
    if(isset($actions[$contact["id"]])) {
      $action = $actions[$contact["id"]];
    }

    if($action == "delete") {
      $contacts["values"][$index] = array(
        "id"          => $contacts["values"][$index]["id"],
        "first_name"  => $contacts["values"][$index]["first_name"],
        "last_name"   => $contacts["values"][$index]["last_name"],
      );
    }

    $contacts["values"][$index]["action"] = $action;
  }

  return $contacts;
}

function getCCACustomKey() {
    $customfield_result = civicrm_api3('CustomField', 'getsingle', array(
        'sequential' => 1,
        'return' => array("id"),
        'name' => "Sync_to_CCA",
    ));
    $cca_sync_custom_id = $customfield_result["id"];
    $cca_sync_custom_key = "custom_".$cca_sync_custom_id;
    return $cca_sync_custom_key;
}

function getContacts($bycontactids = FALSE, $contactids = array()) {
  $isCiviTeamsInstalled = isCiviTeamsExtensionInstalled();

  $teams = array();
  if($isCiviTeamsInstalled) {
      $teams = getContactTeams();
  }

  $contactparams = array(
    'sequential'    => 1,
    'return'        => array("first_name","last_name","sort_name","image_URL","created_date","modified_date","group"),
    'api.Email.get' => array('return' => array("location_type_id", "email")),
    'api.Phone.get' => array('return' => array("location_type_id","phone_type_id", "phone")),
    'options'       => array('limit'  => -1)
  );

  if(!$bycontactids) {
    if($isCiviTeamsInstalled && count($teams) == 0) {
      $contactparams["group"] = array('IN' => array(
        "-1"
      ));
    } else {
      $group_ids = getCCAActiveGroups();

      if($isCiviTeamsInstalled) {
        $teamGroups = getTeamGroups($teams, TRUE);
        $teamGroups = array_column($teamGroups["values"], "entity_id");
        $group_ids = array_intersect($group_ids, $teamGroups);
      }

      if(count($group_ids) == 0) {
        $group_ids[] = "-1";
      }
      $contactparams["group"] = array('IN' => $group_ids);
    }
  } else {
    if(count($contactids) == 0) {
      $contactids[] = "-1";
    }
    $contactparams["id"] = array('IN' => $contactids);
  }

  $contacts = civicrm_api3('Contact', 'get', $contactparams);

  return $contacts;
}
