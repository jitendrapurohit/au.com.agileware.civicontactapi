<?php

use CRM_Civicontact_ExtensionUtil as E;

/**
 * Relationship.Favourites API specification
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_relationship_Favourites_spec(&$spec) {
  $spec['contact_id'] = [
    'api.required' => 1,
    'title' => 'Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'description' => 'Contact ID of the user for which you want to find Favourites.',
    'FKClassName' => 'CRM_Contact_DAO_Contact',
    'FKApiName' => 'Contact',
  ];
}

/**
 * Relationship.Favourites API
 *
 * @param array $params
 *
 * @return array API result descriptor
 */
function civicrm_api3_relationship_Favourites($params) {
  $contactID = $params["contact_id"];

  $sequential = 0;
  if (isset($params["sequential"]) && $params["sequential"]) {
    $sequential = 1;
  }

  $relationshiptype = civicrm_api3('RelationshipType', 'get', [
    'name_a_b' => "has favourited",
    "sequential" => 1,
  ]);

  if ($relationshiptype["count"] == 0) {
    $relationshiptype = createFavouriteRelationshipType();
  }
  else {
    $relationshiptype = $relationshiptype["values"][0];
  }

  if ($relationshiptype != NULL) {
    $apiparams = [
      'contact_id_a' => $contactID,
      'relationship_type_id' => $relationshiptype["id"],
      'sequential' => $sequential,
      'options' => ['sort' => "id DESC"],
    ];
    $relation = civicrm_api3('Relationship', 'get', $apiparams);
    $currentDateTime = new \DateTime();
    $relation["timestamp"] = $currentDateTime->format("Y-m-d H:i:s");
    return $relation;
  }

  throw new API_Exception('Favourites not found for ' . $contactID, 404);
}


/**
 * Create favourite relationship type
 *
 * @return Array of relationship type values
 */
function createFavouriteRelationshipType() {
  $relationshiptype = civicrm_api3('RelationshipType', 'create', [
    "name_a_b" => "has favourited",
    "name_b_a" => "is favourited by",
    "label_a_b" => "Has Favourited",
    "label_b_a" => "Is Favourited By",
    "description" => "Relationship to mark any contact as a favourite of another contact",
    "contact_type_a" => "Individual",
    "contact_type_b" => "Individual",
    "is_active" => 1,
    "is_reserved" => 0,
    "sequential" => 1,
  ]);
  return $relationshiptype;
}
