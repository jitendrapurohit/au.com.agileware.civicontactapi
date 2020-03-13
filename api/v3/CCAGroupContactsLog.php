<?php

use CRM_Civicontact_ExtensionUtil as E;

/**
 * CCAGroupContactsLog.getmodifiedcontacts API specification
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_c_c_a_group_contacts_log_getmodifiedcontacts_spec(&$spec) {
  $spec['createdat'] = [
    'api.required' => 0,
    'title' => 'Created At',
    'type' => CRM_Utils_Type::T_TIMESTAMP,
  ];
}

/**
 * CCAGroupContactsLog.create API specification
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_c_c_a_group_contacts_log_create_spec(&$spec) {
  $spec['contactid'] = [
    'api.required' => 1,
    'title' => 'Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'FKClassName' => 'CRM_Contact_DAO_Contact',
    'FKApiName' => 'Contact',
  ];
  $spec['groupid'] = [
    'api.required' => 1,
    'title' => 'Group ID',
    'type' => CRM_Utils_Type::T_INT,
    'FKClassName' => 'CRM_Contact_DAO_Group',
    'FKApiName' => 'Group',
  ];
  $spec['action'] = [
    'api.required' => 1,
    'title' => 'Action',
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * CCAGroupContactsLog.create API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_c_c_a_group_contacts_log_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * CCAGroupContactsLog.countcontact API
 *
 * @param array $params
 *
 * @return array
 * @throws \CiviCRM_API3_Exception
 */
function civicrm_api3_c_c_a_group_contacts_log_countcontact($params) {
  $params["group"] = ['IN' => getCCAActiveGroups()];
  $params['first_name'] = ['IS NOT NULL' => 1];
  $params['return'] = ['id'];
  $params['options'] = ['limit' => 0];
  $contacts = civicrm_api3('Contact', 'get', $params);
  $ids = [];
  if (!$contacts['is_error']) {
    foreach ($contacts['values'] as $contact) {
      $ids[] = $contact['contact_id'];
    }
  }
  $contacts['values'] = $ids;
  return $contacts;
}

/**
 * CCAGroupContactsLog.get API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_c_c_a_group_contacts_log_getmodifiedcontacts($params) {
  if (isset($params["createdat"]) && isset($params["createdat"][">="]) && $params["createdat"][">="] != "") {

    $isCiviTeamsInstalled = isCiviTeamsExtensionInstalled();

    $groupslogparams = [
      'sequential' => 1,
      'return' => ["groupid", "action", "groupid.title"],
      'createdat' => $params["createdat"],
      'options' => ['sort' => "id DESC", 'limit' => 0],
    ];

    $uniqueGroups = [];
    $logGroupsAdded = [];

    if ($isCiviTeamsInstalled) {
      $teamgroupsToCheck = [];
      $teamGroupsToDelete = [];

      $teams = getContactTeams();
      $teamsToRemove = [];
      $modifiedTeams = getModifiedTeams($params["createdat"]);
      $teamsToAdd = [];

      foreach ($modifiedTeams["values"] as $modifiedTeam) {
        if (!$modifiedTeam["status"]) {
          $teamsToRemove[] = $modifiedTeam["team_id"];
        }

        if ($modifiedTeam["status"]) {
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

      if (count($modifiedteamgroups) > 0) {
        foreach ($modifiedteamgroups as $index => $teamgroup) {
          if ($teamgroup["isactive"]) {
            $teamgroupsToCheck[] = $teamgroup["entity_id"];
            $modifiedteamgroups[$index]["isactive"] = "on";
          }
          else {
            $teamGroupsToDelete[] = $teamgroup["entity_id"];
            $modifiedteamgroups[$index]["isactive"] = "off";
          }
        }

        $activeGroups = getCCAActiveGroups($teamgroupsToCheck, TRUE);
        foreach ($activeGroups as $activeGroup) {
          if (!array_key_exists($activeGroup["id"], $logGroupsAdded)) {
            $uniqueGroups[$activeGroup["id"]] = [
              "action" => "on",
              "groupid" => $activeGroup["id"],
              "groupname" => $activeGroup["title"],
            ];
          }
        }

        $groupDetails = getGroupDetailsByIds($teamGroupsToDelete);
        foreach ($groupDetails as $groupDetail) {
          if (!array_key_exists($groupDetail["id"], $logGroupsAdded)) {
            $uniqueGroups[$groupDetail["id"]] = [
              "action" => "off",
              "groupid" => $groupDetail["id"],
              "groupname" => $groupDetail["title"],
            ];
          }
        }

        $uniqueGroupsClone = $uniqueGroups;
        $uniqueGroups = [];

        foreach ($modifiedteamgroups as $teamgroup) {
          if (array_key_exists($teamgroup["entity_id"], $uniqueGroupsClone)) {
            $uniqueGroups[] = $uniqueGroupsClone[$teamgroup["entity_id"]];
          }
        }
      }

      $teamgroupsToCheck = [];
      foreach ($teamgroups as $teamgroup) {
        if ($teamgroup["isactive"]) {
          $teamgroupsToCheck[] = $teamgroup["entity_id"];
          $modifiedteamgroups[$index]["isactive"] = "on";
        }
      }

      if (count($teamgroupsToCheck) == 0) {
        $teamgroupsToCheck[] = "-1";
      }

      $groupslogparams["groupid"] = ["IN" => $teamgroupsToCheck];

      if (count($teamgroupsToRemove) > 0) {
        $teamgroupsToRemove = array_column($teamgroupsToRemove, "entity_id");
        $groupDetails = getGroupDetailsByIds($teamgroupsToRemove);
        foreach ($groupDetails as $groupDetail) {
          if (!array_key_exists($groupDetail["id"], $logGroupsAdded)) {
            $uniqueGroups[$groupDetail["id"]] = [
              "action" => "off",
              "groupid" => $groupDetail["id"],
              "groupname" => $groupDetail["title"],
            ];
          }
        }
      }

      if (count($teamGroupsToAdd) > 0) {
        $teamGroupsToAdd = array_column($teamGroupsToAdd, "entity_id");
        $groupDetails = getGroupDetailsByIds($teamGroupsToAdd);
        foreach ($groupDetails as $groupDetail) {
          if (!array_key_exists($groupDetail["id"], $logGroupsAdded)) {
            $uniqueGroups[$groupDetail["id"]] = [
              "action" => "on",
              "groupid" => $groupDetail["id"],
              "groupname" => $groupDetail["title"],
            ];
          }
        }
      }
    }

    $groupslog = civicrm_api3('CCAGroupsLog', 'get', $groupslogparams);

    foreach ($groupslog["values"] as $grouplog) {
      if (!array_key_exists($grouplog["groupid"], $logGroupsAdded)) {
        $uniqueGroups[] = [
          "action" => $grouplog["action"],
          "groupid" => $grouplog["groupid"],
          "groupname" => $grouplog["groupid.title"],
        ];
      }
    }

    $contactsfound = [];
    foreach ($uniqueGroups as $index => $groupinfo) {
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
    foreach ($groupcontactlogs["values"] as $groupcontactlog) {
      if (!array_key_exists($groupcontactlog["contactid"], $contactsfound)) {
        $contactsfound[$groupcontactlog["contactid"]] = $groupcontactlog["action"];
      }
    }

    $modifiedcontactids = getCCAModifiedContactIds($params["createdat"]);
    foreach ($modifiedcontactids as $modifiedcontactid) {
      if (!array_key_exists($modifiedcontactid, $contactsfound)) {
        $contactsfound[$modifiedcontactid] = 'create';
      }
    }

    $contactids = array_keys($contactsfound);

    if ($paramsReturn) {
      $params['return'] = $paramsReturn; //Setting the return params back.
    }
    $contacts = getContacts($params, TRUE, $contactids);
    return tagContacts($contacts, $contactsfound);
  }

  $contacts = getContacts($params);
  return tagContacts($contacts);
}

function getCCAModifiedContactIds($createdat) {
  $group_ids = getCCAActiveGroups();
  if (count($group_ids) == 0) {
    $group_ids = ["-1"];
  }

  $contactids = civicrm_api3("Contact", "get", [
    'IN' => $group_ids,
    'return' => 'id',
    'sequential' => TRUE,
    'options' => [
      'limit' => 0,
    ],
    'modified_date' => $createdat,
  ]);

  $contactids = array_column($contactids["values"], "contact_id");
  return $contactids;
}

function getGroupContacts($groupname) {
  $contactids = civicrm_api3('Contact', 'get', [
    "group" => $groupname,
    "return" => ["id"],
    "sequential" => 1,
    "options" => ["limit" => -1],
  ]);

  $contactids = array_column($contactids["values"], "id");
  return $contactids;
}

function tagContacts($contacts, $actions = []) {
  foreach ($contacts["values"] as $index => $contact) {
    $action = "create";
    if (isset($actions[$contact["id"]])) {
      $action = $actions[$contact["id"]];
    }

    if ($action == "delete") {
      $contacts["values"][$index] = [
        "id" => $contacts["values"][$index]["id"],
        "first_name" => $contacts["values"][$index]["first_name"],
        "last_name" => $contacts["values"][$index]["last_name"],
      ];
    }

    $contacts["values"][$index]["action"] = $action;
  }

  return $contacts;
}

function getCCACustomKey() {
  $customfield_result = civicrm_api3('CustomField', 'getsingle', [
    'sequential' => 1,
    'return' => ["id"],
    'name' => "Sync_to_CCA",
  ]);
  $cca_sync_custom_id = $customfield_result["id"];
  $cca_sync_custom_key = "custom_" . $cca_sync_custom_id;
  return $cca_sync_custom_key;
}

function getContacts($params = [], $bycontactids = FALSE, $contactids = []) {
  $isCiviTeamsInstalled = isCiviTeamsExtensionInstalled();

  $teams = [];
  if ($isCiviTeamsInstalled) {
    $teams = getContactTeams();
  }

  $contactparams = [
    'sequential' => 1,
    'options' => [
      'limit' => 0,
    ],
    'return' => [
      "first_name",
      "last_name",
      "sort_name",
      "image_URL",
      "created_date",
      "modified_date",
      "group",
      "birth_date",
      "current_employer",
      "formal_title",
      "gender",
      "prefix_id",
      "suffix_id",
      "job_title",
      "middle_name",
    ],
    'api.Email.get' => ['return' => ["location_type_id", "email"]],
    'api.Phone.get' => [
      'return' => [
        "location_type_id",
        "phone_type_id",
        "phone",
      ],
    ],
    'api.Address.get' => [
      'return' => [
        "id",
        "name",
        "contact_id",
        "location_type_id",
        "is_primary",
        "is_billing",
        "street_address",
        "street_number",
        "street_number_suffix",
        "street_name",
        "street_type",
        "street_number_postdirectional",
        "city",
        "county_id.name",
        "county_id",
        "state_province_id.name",
        "state_province_id",
        "postal_code",
        "country_id.name",
        "country_id",
        "geo_code_1",
        "geo_code_2",
        "manual_geo_code",
        "supplemental_address_1",
        "supplemental_address_2",
        "supplemental_address_3",
      ],
    ],
    'api.Website.get' => [
      'return' => [
        "id",
        "contact_id",
        "url",
        "website_type_id",
        "website_type_id.label",
        "website_type_id.name",
      ],
    ],
    'options' => ['limit' => -1],
  ];

  $selectedProfileFields = civicrm_api3("UFGroup", "ccaprofilefields", [
    'selectionoptionswithkeys' => TRUE,
  ]);
  $selectedProfileFields = $selectedProfileFields["values"];
  $selectedCustomProfileFields = _cca_selected_custom_profile_fields($selectedProfileFields);
  if (count($selectedCustomProfileFields)) {
    $customFieldsToAdd = array_column($selectedCustomProfileFields, "db_field_name");
    $contactparams['return'] = array_merge($contactparams['return'], $customFieldsToAdd);
  }

  if (!$bycontactids) {
    if ($isCiviTeamsInstalled && count($teams) == 0) {
      $contactparams["group"] = [
        'IN' => [
          "-1",
        ],
      ];
    }
    else {
      $group_ids = getCCAActiveGroups();

      if ($isCiviTeamsInstalled) {
        $teamGroups = getTeamGroups($teams, TRUE);
        $teamGroups = array_column($teamGroups["values"], "entity_id");
        $group_ids = array_intersect($group_ids, $teamGroups);
      }

      if (count($group_ids) == 0) {
        $group_ids[] = "-1";
      }
      $contactparams["group"] = ['IN' => $group_ids];
    }
  }
  else {
    if (count($contactids) == 0) {
      $contactids[] = "-1";
    }
    $contactparams["id"] = ['IN' => $contactids];
  }

  if (isset($params["return"]) && count($params["return"]) == 1 && $params["return"][0] = 'contact_id') {
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

  $addressFieldsBAO = [
    'CRM_Core_BAO_Address',
    'CRM_Core_BAO_Country',
    'CRM_Core_DAO_County',
    'CRM_Core_DAO_StateProvince',
  ];

  $websiteFieldBAO = 'CRM_Core_BAO_Website';
  $contactFieldBAO = 'CRM_Contact_BAO_Contact';

  $countriesToFind = [];
  $statesToFind = [];

  foreach ($selectedProfileFields as $selectedProfileField) {
    $fieldtocheck = $selectedProfileField["name"];

    foreach ($contacts["values"] as $contactindex => $contact) {
      $fieldValue = "";
      $fieldLabel = "";
      if (($selectedProfileField['bao'] == $contactFieldBAO || $selectedProfileField['bao'] == '') && isset($contact[$fieldtocheck])) {
        $fieldValue = $contact[$fieldtocheck];
        unset($contacts["values"][$contactindex][$fieldtocheck]);
        if ($fieldtocheck != $selectedProfileField["name"]) {
          unset($contacts["values"][$contactindex][$selectedProfileField["name"]]);
        }

        $labelFields = [
          "gender_id" => "gender",
          "prefix_id" => "individual_prefix",
          "suffix_id" => "individual_suffix",
        ];

        if (array_key_exists($fieldtocheck, $labelFields)) {
          $fieldLabel = $contact[$labelFields[$fieldtocheck]];
        }
        else {
          if (isset($selectedProfileField["selectionvalues"])) {
            $fieldLabel = [];
            $fieldValueToCheck = $fieldValue;
            if (!is_array($fieldValue)) {
              $fieldValueToCheck = [$fieldValue];
            }

            foreach ($fieldValueToCheck as $valuetocheck) {
              if (isset($selectedProfileField["selectionvalues"][$valuetocheck])) {
                $fieldLabel[] = $selectedProfileField["selectionvalues"][$valuetocheck]["val"];
              }
            }

            if (count($fieldLabel) == 1) {
              $fieldLabel = $fieldLabel[0];
            }
          }
          else {
            if ($selectedProfileField["html_type"] == "Select Country" && $fieldValue != '') {
              $countriesToFind[] = $fieldValue;
            }
            else {
              if ($selectedProfileField["html_type"] == "Multi-Select Country" && $fieldValue != '') {
                $countriesToFind = array_merge($countriesToFind, $fieldValue);
              }
              else {
                if ($selectedProfileField["html_type"] == "Multi-Select State/Province" && $fieldValue != '') {
                  $statesToFind = array_merge($statesToFind, $fieldValue);
                }
                else {
                  if ($selectedProfileField["html_type"] == "Select State/Province" && $fieldValue != '') {
                    $statesToFind[] = $fieldValue;
                  }
                }
              }
            }
          }
        }
      }
      if (($selectedProfileField['bao'] == $websiteFieldBAO) && isset($contact['websitefields'][$fieldtocheck])) {
        $fieldValue = $contact['websitefields'][$fieldtocheck];
      }
      if (in_array($selectedProfileField['bao'], $addressFieldsBAO) && isset($contact['addressfields'][$fieldtocheck])) {
        $fieldValue = $contact['addressfields'][$fieldtocheck];
        if (isset($contact['addressfields'][$fieldtocheck . '-label'])) {
          $fieldLabel = $contact['addressfields'][$fieldtocheck . '-label'];
        }
      }

      if ($selectedProfileField["html_type"] == "Select Date" && $fieldValue != "") {
        $fieldLabel = CRM_Utils_Date::customFormat($fieldValue, $selectedProfileField["smarty_view_format"]);
      }

      $contacts["values"][$contactindex]["profilefields"][] = [
        'key' => $selectedProfileField["name"],
        'value' => $fieldValue,
        'html_type' => $selectedProfileField["html_type"],
        'label' => ($fieldLabel) ? $fieldLabel : $fieldValue,
      ];
    }
  }

  $countriesToFind = array_unique($countriesToFind);
  $statesToFind = array_unique($statesToFind);

  _cca_group_contacts_add_country_fields($contacts, $countriesToFind);
  _cca_group_contacts_add_state_fields($contacts, $statesToFind);

  _cca_group_contacts_clean_contact_fiels($contacts);
}

/**
 * Return filtered custom profile fields.
 *
 * @param $profilefields
 *
 * @return array
 */
function _cca_selected_custom_profile_fields($profilefields) {
  $customProfileFields = [];
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
      $contacts["values"][$index][$addressfieldskey] = [];
    }
    if (isset($contact["api.Address.get"])) {
      $addresses = $contact["api.Address.get"]["values"];
      foreach ($addresses as $address) {
        $locationid = 'Primary';
        $secondarylocationid = 0;

        if (isset($address['location_type_id'])) {
          if ($address['is_primary']) {
            $secondarylocationid = $address['location_type_id'];
          }
          else {
            $locationid = $address['location_type_id'];
          }
        }
        $addressFields = _cca_group_contacts_get_address_fields($locationid, $address);
        $contacts["values"][$index][$addressfieldskey] = array_merge($contacts["values"][$index][$addressfieldskey], $addressFields);

        if ($secondarylocationid) {
          $addressFields = _cca_group_contacts_get_address_fields($secondarylocationid, $address);
          $contacts["values"][$index][$addressfieldskey] = array_merge($contacts["values"][$index][$addressfieldskey], $addressFields);
        }
      }
      unset($contacts["values"][$index]["api.Address.get"]);
    }
  }
}

function _cca_group_contacts_get_address_fields($locationid, $address) {
  return [
    'address_name-' . $locationid => (isset($address["name"])) ? $address["name"] : '',
    'country-' . $locationid => (isset($address["country_id"])) ? $address["country_id"] : '',
    'country-' . $locationid . '-label' => (isset($address["country_id.name"])) ? $address["country_id.name"] : '',
    'county-' . $locationid => (isset($address["county_id"])) ? $address["county_id"] : '',
    'county-' . $locationid . '-label' => (isset($address["county_id.name"])) ? $address["county_id.name"] : '',
    'city-' . $locationid => (isset($address["city"])) ? $address["city"] : '',
    'postal_code-' . $locationid => (isset($address["postal_code"])) ? $address["postal_code"] : '',
    'state_province-' . $locationid => (isset($address["state_province_id"])) ? $address["state_province_id"] : '',
    'state_province-' . $locationid . '-label' => (isset($address["state_province_id.name"])) ? $address["state_province_id.name"] : '',
    'street_address-' . $locationid => (isset($address["street_address"])) ? $address["street_address"] : '',
    'supplemental_address_1-' . $locationid => (isset($address["supplemental_address_1"])) ? $address["supplemental_address_1"] : '',
    'supplemental_address_2-' . $locationid => (isset($address["supplemental_address_2"])) ? $address["supplemental_address_2"] : '',
    'supplemental_address_3-' . $locationid => (isset($address["supplemental_address_3"])) ? $address["supplemental_address_3"] : '',
  ];
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
      $contacts["values"][$index][$websitefieldskey] = [];
    }
    if (isset($contact["api.Website.get"])) {
      $websites = $contact["api.Website.get"]["values"];
      foreach ($websites as $website) {
        $websitetypeid = $website['website_type_id'];
        $websiteFields = [
          'url-' . $websitetypeid => (isset($website["url"])) ? $website["url"] : '',
        ];
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
  foreach ($contacts["values"] as $index => $contact) {
    $contacts["values"][$index]["profilefields"] = [];
  }
}

/**
 * Clean contacts array to remove unwanted field values.
 *
 * @param $contacts
 */
function _cca_group_contacts_clean_contact_fiels(&$contacts) {
  foreach ($contacts["values"] as $index => $contact) {
    unset($contacts["values"][$index]["addressfields"]);
    unset($contacts["values"][$index]["websitefields"]);
  }
}

/**
 * Add custom country values in contact array.
 *
 * @param $contacts
 */
function _cca_group_contacts_add_country_fields(&$contacts, $countries) {
  if (count($countries)) {
    $countries = civicrm_api3("Country", "get", [
      "id" => ["IN" => $countries],
      'options' => [
        'limit' => 0,
      ],
    ]);

    $countries = $countries["values"];

    foreach ($contacts["values"] as $index => $contact) {
      foreach ($contact["profilefields"] as $profilefieldindex => $profilefield) {
        if ($profilefield["html_type"] == "Select Country" && $profilefield["value"] != '' && array_key_exists($profilefield["value"], $countries)) {
          $contacts["values"][$index]["profilefields"][$profilefieldindex]["label"] = $countries[$profilefield["value"]]["name"];
        }

        if ($profilefield["html_type"] == "Multi-Select Country" && $profilefield["value"] != '') {
          $contacts["values"][$index]["profilefields"][$profilefieldindex]["label"] = [];
          foreach ($profilefield["value"] as $countryvalue) {
            if (array_key_exists($countryvalue, $countries)) {
              $contacts["values"][$index]["profilefields"][$profilefieldindex]["label"][] = $countries[$countryvalue]["name"];
            }
          }
        }
      }
    }
  }
}

/**
 * Add custom state values in contact array.
 *
 * @param $contacts
 */
function _cca_group_contacts_add_state_fields(&$contacts, $states) {
  if (count($states)) {
    $states = civicrm_api3("StateProvince", "get", [
      "id" => ["IN" => $states],
      'options' => [
        'limit' => 0,
      ],
    ]);

    $states = $states["values"];

    foreach ($contacts["values"] as $index => $contact) {
      foreach ($contact["profilefields"] as $profilefieldindex => $profilefield) {
        if ($profilefield["html_type"] == "Select State/Province" && $profilefield["value"] != '' && array_key_exists($profilefield["value"], $states)) {
          $contacts["values"][$index]["profilefields"][$profilefieldindex]["label"] = $states[$profilefield["value"]]["name"];
        }

        if ($profilefield["html_type"] == "Multi-Select State/Province" && $profilefield["value"] != '') {
          $contacts["values"][$index]["profilefields"][$profilefieldindex]["label"] = [];
          foreach ($profilefield["value"] as $countryvalue) {
            if (array_key_exists($countryvalue, $states)) {
              $contacts["values"][$index]["profilefields"][$profilefieldindex]["label"][] = $states[$countryvalue]["name"];
            }
          }
        }

        unset($contacts["values"][$index]["profilefields"][$profilefieldindex]["html_type"]);
      }
    }
  }
}