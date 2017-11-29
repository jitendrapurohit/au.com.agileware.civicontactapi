<?php
use CRM_Civicontactsapp_ExtensionUtil as E;

/**
 * Relationship.Favourites API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_relationship_Favourites_spec(&$spec) {
  $spec['contact_id'] = array(
    'api.required' => 1,
    'title' => 'Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'description'  => 'Contact ID of the user for which you want to find Favourites.',
    'FKClassName' => 'CRM_Contact_DAO_Contact',
    'FKApiName' => 'Contact',
  );
}

/**
 * Relationship.Favourites API
 *
 * @param array $params
 * @return array API result descriptor
 */
function civicrm_api3_relationship_Favourites($params) {
  $contactID = $params["contact_id"];

  $sequential = 0;
  if(isset($params["sequential"]) && $params["sequential"]) {
    $sequential = 1;
  }

  $relationshiptype = civicrm_api3('RelationshipType', 'get', array(
    'name_a_b'   => "has favourited",
    "sequential" => 1,
  ));

  if($relationshiptype["count"] > 0) {
    $relationshiptype = $relationshiptype["values"][0];
    $apiparams = array(
      'contact_id_a'         => $contactID,
      'relationship_type_id' => $relationshiptype["id"],
      'sequential'           => $sequential,
      'options'              => array('sort' => "id DESC"),
    );
    $relation = civicrm_api3('Relationship', 'get', $apiparams);
    $currentDateTime = new \DateTime();
    $relation["timestamp"] = $currentDateTime->format("Y-m-d H:i:s");
    return $relation;
  }

  throw new API_Exception('Favourites not found for '.$contactID, 404);
}
