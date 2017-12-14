<?php
use CRM_Civicontactsapp_ExtensionUtil as E;

class CRM_Civicontactsapp_BAO_CCAGroupsLog extends CRM_Civicontactsapp_DAO_CCAGroupsLog {

  /**
   * Create a new CCAGroupsLog based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Civicontactsapp_DAO_CCAGroupsLog|NULL
   *

   */

   /*
  public static function create($params) {
    $className = 'CRM_Civicontactsapp_DAO_CCAGroupsLog';
    $entityName = 'CCAGroupsLog';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
