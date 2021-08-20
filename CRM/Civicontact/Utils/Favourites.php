<?php

use CRM_Civicontact_ExtensionUtil as E;

class CRM_Civicontact_Utils_Favourites {

  /**
   * Create favourite relationship type
   */
  public static function createFavouriteRelationshipType() : array {
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

}