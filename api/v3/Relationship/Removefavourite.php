<?php
use CRM_Civicontactsapp_ExtensionUtil as E;

/**
 * Relationship.Removefavourite API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_relationship_Removefavourite_spec(&$spec) {
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
 * Relationship.Removefavourite API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_relationship_Removefavourite($params) {
  $contact_id_a = $params["contact_id_a"];
  $contact_id_b = $params["contact_id_b"];

  $relationshiptype = civicrm_api3('RelationshipType', 'get', array(
    'name_a_b'   => "has favourited",
    "sequential" => 1,
  ));

  if($relationshiptype["count"] > 0) {
    $relationshiptype = $relationshiptype["values"][0];
    $relation = civicrm_api3('Relationship', 'get', array(
      'contact_id_a'         => $contact_id_a,
      'contact_id_b'         => $contact_id_b,
      'relationship_type_id' => $relationshiptype["id"],
    ));
    if($relation["count"] > 0) {
      $relationid = $relation["id"];
      $deleted = civicrm_api3('Relationship', 'delete', array(
        'id'         => $relationid,
      ));
      $deleted["values"] = "Contact removed from favourites successfully.";
      return $deleted;
    }
  }
  throw new API_Exception('Favourite relationship not found between '.$contact_id_a.' and '.$contact_id_b, 404);
}
