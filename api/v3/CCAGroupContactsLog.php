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

    $paramsReturn = NULL;
    if (isset($params['return'])) {
      $paramsReturn = $params['return']; //Stroing return to use the same params in fetching Groups.
      unset($params['return']);
    }

    $groupcontactlogs = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
    foreach($groupcontactlogs["values"] as $groupcontactlog) {
      if(!array_key_exists($groupcontactlog["contactid"], $contactsfound)) {
        $contactsfound[$groupcontactlog["contactid"]] = $groupcontactlog["action"];
      }
    }

    $modifiedcontactids = getCCAModifiedContactIds($params["createdat"]);
    foreach($modifiedcontactids as $modifiedcontactid) {
      if(!array_key_exists($modifiedcontactid, $contactsfound)) {
          $contactsfound[$modifiedcontactid] = 'create';
      }
    }

    $contactids = array_keys($contactsfound);

    if ($paramsReturn) {
      $params['return'] = $paramsReturn; //Setting the return params back.
    }
    $contacts = getContacts($params,TRUE, $contactids);
    return tagContacts($contacts, $contactsfound);
  }

  $contacts = getContacts($params);
  return tagContacts($contacts);
}

function getCCAModifiedContactIds($createdat) {
  $group_ids = getCCAActiveGroups();
  if(count($group_ids) == 0) {
    $group_ids = array("-1");
  }

  $contactids = civicrm_api3("Contact", "get", array(
    'IN'            => $group_ids,
    'return'        => 'id',
    'sequential'    => TRUE,
    'modified_date' => $createdat,
  ));

  $contactids = array_column($contactids["values"], "contact_id");
  return $contactids;
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

function getContacts($params = array(), $bycontactids = FALSE, $contactids = array()) {
  $isCiviTeamsInstalled = isCiviTeamsExtensionInstalled();

  $teams = array();
  if($isCiviTeamsInstalled) {
      $teams = getContactTeams();
  }

  $contactparams = array(
    'sequential'      => 1,
    'return'          => array("first_name","last_name","sort_name","image_URL","created_date","modified_date","group", "birth_date", "current_employer", "formal_title", "gender", "prefix_id", "suffix_id", "job_title", "middle_name"),
    'api.Email.get'   => array('return' => array("location_type_id", "email")),
    'api.Phone.get'   => array('return' => array("location_type_id","phone_type_id", "phone")),
    'api.Address.get' => array('return' => array("id", "name", "contact_id", "location_type_id", "is_primary", "is_billing", "street_address", "street_number", "street_number_suffix", "street_name", "street_type", "street_number_postdirectional", "city", "county_id.name", "county_id", "state_province_id.name", "state_province_id", "postal_code", "country_id.name", "country_id", "geo_code_1", "geo_code_2", "manual_geo_code", "supplemental_address_1", "supplemental_address_2", "supplemental_address_3")),
    'api.Website.get' => array('return' => array("id", "contact_id", "url", "website_type_id", "website_type_id.label", "website_type_id.name")),
    'options'         => array('limit'  => -1)
  );

  $selectedProfileFields = getCCASelectedProfileFields();
  $selectedCustomProfileFields = _cca_selected_custom_profile_fields($selectedProfileFields);
  if (count($selectedCustomProfileFields)) {
    $customFieldsToAdd = array_column($selectedCustomProfileFields, "db_field_name");
    $contactparams['return'] = array_merge($contactparams['return'], $customFieldsToAdd);
  }

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

  if(isset($params["return"]) && count($params["return"]) == 1 && $params["return"][0] = 'contact_id') {
      $contactparams["return"] = $params["return"];
      unset($contactparams["api.Email.get"]);
      unset($contactparams["api.Phone.get"]);
  }

  $contacts = civicrm_api3('Contact', 'get', $contactparams);
  if (count($selectedProfileFields)) {
    _cca_group_contacts_add_profile_fields($contacts, $selectedProfileFields);
  }

  return $contacts;
}

function _cca_group_contacts_add_profile_fields(&$contacts, $selectedProfileFields) {
  _cca_group_contacts_add_address_fields($contacts);
  _cca_group_contacts_add_website_fields($contacts);
  _cca_group_contacts_add_profile_fields_key($contacts);

  $addressFieldsBAO = array(
    'CRM_Core_BAO_Address',
    'CRM_Core_BAO_Country',
    'CRM_Core_DAO_County',
    'CRM_Core_DAO_StateProvince',
  );

  $websiteFieldBAO = 'CRM_Core_BAO_Website';
  $contactFieldBAO = 'CRM_Contact_BAO_Contact';

  foreach ($selectedProfileFields as $selectedProfileField) {

    $fieldtocheck = $selectedProfileField["name"];

    foreach ($contacts["values"] as $contactindex => $contact) {
      $fieldValue = "";
      if (($selectedProfileField['bao'] == $contactFieldBAO || $selectedProfileField['bao'] == '') && isset($contact[$fieldtocheck])) {
        $fieldValue = $contact[$fieldtocheck];
        unset($contacts["values"][$contactindex][$fieldtocheck]);
        if($fieldtocheck != $selectedProfileField["name"]) {
          unset($contacts["values"][$contactindex][$selectedProfileField["name"]]);
        }
      }
      if (($selectedProfileField['bao'] == $websiteFieldBAO) && isset($contact['websitefields'][$fieldtocheck])) {
        $fieldValue = $contact['websitefields'][$fieldtocheck];
      }
      if (in_array($selectedProfileField['bao'], $addressFieldsBAO) && isset($contact['addressfields'][$fieldtocheck])) {
        $fieldValue = $contact['addressfields'][$fieldtocheck];
      }
      $contacts["values"][$contactindex]["profilefields"][] = array(
        'key' => $selectedProfileField["name"],
        'value' => $fieldValue,
      );
    }
  }

  _cca_group_contacts_clean_contact_fiels($contacts);
}

/**
 * Return filtered custom profile fields.
 *
 * @param $profilefields
 * @return array
 */
function _cca_selected_custom_profile_fields($profilefields) {
    $customProfileFields = array();
    foreach ($profilefields as $profilefield) {
      if (isProfileFieldCustom($profilefield["name"])) {
        $customProfileFields[] = $profilefield;
      }
    }
    return $customProfileFields;
}

/**
 * Modify Contacts array to generate addressfields values.
 *
 * @param $contacts
 */
function _cca_group_contacts_add_address_fields(&$contacts) {
  $addressfieldskey = "addressfields";
  foreach ($contacts["values"] as $index => $contact) {
    if (!isset($contacts["values"][$index][$addressfieldskey])) {
      $contacts["values"][$index][$addressfieldskey] = array();
    }
    if (isset($contact["api.Address.get"])) {
      $addresses = $contact["api.Address.get"]["values"];
      foreach ($addresses as $address) {
        $locationid = 'Primary';
        $secondarylocationid = 0;

        if (isset($address['location_type_id'])) {
          if ($address['is_primary']) {
            $secondarylocationid = $address['location_type_id'];
          } else {
            $locationid = $address['location_type_id'];
          }
        }
        $addressFields = _cca_group_contacts_get_address_fields($locationid, $address);
        $contacts["values"][$index][$addressfieldskey] = array_merge($contacts["values"][$index][$addressfieldskey], $addressFields);

        if($secondarylocationid) {
          $addressFields = _cca_group_contacts_get_address_fields($secondarylocationid, $address);
          $contacts["values"][$index][$addressfieldskey] = array_merge($contacts["values"][$index][$addressfieldskey], $addressFields);
        }
      }
      unset($contacts["values"][$index]["api.Address.get"]);
    }
  }
}

function _cca_group_contacts_get_address_fields($locationid, $address) {
  return array(
    'address_name-'.$locationid => (isset($address["name"])) ? $address["name"] : '',
    'country-'.$locationid => (isset($address["country_id"])) ? $address["country_id"] : '',
    'county-'.$locationid => (isset($address["county_id"])) ? $address["county_id"] : '',
    'city-'.$locationid => (isset($address["city"])) ? $address["city"] : '',
    'postal_code-'.$locationid => (isset($address["postal_code"])) ? $address["postal_code"] : '',
    'state_province-'.$locationid => (isset($address["state_province_id"])) ? $address["state_province_id"] : '',
    'street_address-'.$locationid => (isset($address["street_address"])) ? $address["street_address"] : '',
    'supplemental_address_1-'.$locationid => (isset($address["supplemental_address_1"])) ? $address["supplemental_address_1"] : '',
    'supplemental_address_2-'.$locationid => (isset($address["supplemental_address_2"])) ? $address["supplemental_address_2"] : '',
    'supplemental_address_3-'.$locationid => (isset($address["supplemental_address_3"])) ? $address["supplemental_address_3"] : '',
  );
}

/**
 * Modify Contacts array to generate websitefields values.
 *
 * @param $contacts
 */
function _cca_group_contacts_add_website_fields(&$contacts) {
  $websitefieldskey = "websitefields";
  foreach ($contacts["values"] as $index => $contact) {
    if (!isset($contacts["values"][$index][$websitefieldskey])) {
      $contacts["values"][$index][$websitefieldskey] = array();
    }
    if (isset($contact["api.Website.get"])) {
      $websites = $contact["api.Website.get"]["values"];
      foreach ($websites as $website) {
        $websitetypeid = $website['website_type_id'];
        $websiteFields = array(
          'url-'.$websitetypeid => (isset($website["url"])) ? $website["url"] : '',
        );
        $contacts["values"][$index][$websitefieldskey] = array_merge($contacts["values"][$index][$websitefieldskey], $websiteFields);
      }
      unset($contacts["values"][$index]["api.Website.get"]);
    }
  }
}

/**
 * Modify contacts array to add profilefields key.
 *
 * @param $contacts
 */
function _cca_group_contacts_add_profile_fields_key(&$contacts) {
  foreach($contacts["values"] as $index => $contact) {
    $contacts["values"][$index]["profilefields"] = array();
  }
}

/**
 * Clean contacts array to remove unwanted field values.
 *
 * @param $contacts
 */
function _cca_group_contacts_clean_contact_fiels(&$contacts) {
  foreach($contacts["values"] as $index => $contact) {
    unset($contacts["values"][$index]["addressfields"]);
    unset($contacts["values"][$index]["websitefields"]);
  }
}