<?php
use CRM_Civicontact_ExtensionUtil as E;

/**
 * Relationship.Markfavourite API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_relationship_Markfavourite_spec(&$spec) {
  $spec['contact_id_a'] = array(
    'api.required' => 1,
    'title' => 'Contact ID A',
    'type' => CRM_Utils_Type::T_INT,
    'description'  => 'Contact ID of the user who has favourited another user.',
    'FKClassName' => 'CRM_Contact_DAO_Contact',
    'FKApiName' => 'Contact',
  );
  $spec['contact_id_b'] = array(
    'api.required' => 1,
    'title' => 'Contact ID B',
    'type' => CRM_Utils_Type::T_INT,
    'description'  => 'Contact ID of the user being favourited.',
    'FKClassName' => 'CRM_Contact_DAO_Contact',
    'FKApiName' => 'Contact',
  );
}

/**
 * Relationship.Markfavourite API
 *
 * @param array $params
 * @return array API result descriptor
 */
function civicrm_api3_relationship_Markfavourite($params) {
  $contact_id_a = $params["contact_id_a"];
  $contact_id_b = $params["contact_id_b"];

  $relationshiptype = civicrm_api3('RelationshipType', 'get', array(
    'name_a_b'   => "has favourited",
    "sequential" => 1,
  ));

  if($relationshiptype["count"] == 0) {
    $relationshiptype = createFavouriteRelationshipType();
  }

  $relationshiptype = $relationshiptype["values"][0];

  $relation = civicrm_api3('Relationship', 'get', array(
    'contact_id_a'         => $contact_id_a,
    'contact_id_b'         => $contact_id_b,
    'relationship_type_id' => $relationshiptype["id"],
    'start_date'           => date("Y-m-d"),
  ));

  if($relation["count"] > 0) {
    throw new API_Exception('Duplicate relationship found between '.$contact_id_a.' and '.$contact_id_b, 405);
  }

  $relation = civicrm_api3('Relationship', 'create', array(
    'contact_id_a'         => $contact_id_a,
    'contact_id_b'         => $contact_id_b,
    'relationship_type_id' => $relationshiptype["id"],
  ));

  return $relation;
}

/**
 * Create favourite relationship type
 * @return Array of relationship type values
 */
function createFavouriteRelationshipType() {
  $relationshiptype = civicrm_api3('RelationshipType', 'create', array(
    "name_a_b"       => "has favourited",
    "name_b_a"       => "is favourited by",
    "label_a_b"      => "Has Favourited",
    "label_b_a"      => "Is Favourited By",
    "description"    => "Relationship to mark any contact as a favourite of another contact",
    "contact_type_a" => "Individual",
    "contact_type_b" => "Individual",
    "is_active"      => 1,
    "is_reserved"    => 0,
    "sequential"     => 1,
  ));
  return $relationshiptype;
}
