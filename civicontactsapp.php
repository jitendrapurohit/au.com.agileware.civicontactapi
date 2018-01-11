<?php

require_once 'civicontactsapp.civix.php';
use CRM_Civicontactsapp_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civicontactsapp_civicrm_config(&$config) {
  _civicontactsapp_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civicontactsapp_civicrm_xmlMenu(&$files) {
  _civicontactsapp_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civicontactsapp_civicrm_install() {
  _civicontactsapp_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function civicontactsapp_civicrm_postInstall() {
  _civicontactsapp_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civicontactsapp_civicrm_uninstall() {
  _civicontactsapp_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civicontactsapp_civicrm_enable() {
  _civicontactsapp_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civicontactsapp_civicrm_disable() {
  _civicontactsapp_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civicontactsapp_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civicontactsapp_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civicontactsapp_civicrm_caseTypes(&$caseTypes) {
  _civicontactsapp_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function civicontactsapp_civicrm_angularModules(&$angularModules) {
  _civicontactsapp_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civicontactsapp_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civicontactsapp_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_tabset().
 */
function civicontactsapp_civicrm_tabset($tabsetName, &$tabs, $context) {
  if($tabsetName == "civicrm/contact/view") {
    if (!empty($context)) {
      $session = CRM_Core_Session::singleton();
      $contactID = $context["contact_id"];
      $isMe = ($contactID == $session->get('userID'));

      if($isMe) {
        $tabscount = count($tabs);
        if(isset($tabs[$tabscount - 1]["weight"])) {
          $weight = ($tabs[$tabscount - 1]["weight"]) + 10;
        } else {
          $weight = ($tabscount+1) * 10;
        }
        $url = CRM_Utils_System::url( 'civicrm/contact/view/qrcode', "cid=$contactID");
        $tab = array(
          'title'   => ts('CCA QR Code'),
          'url'    => $url,
          'valid'   => 1,
          'active'  => 1,
          'weight'  => $weight,
          'current' => false,
        );
        $tabs[] = $tab;
      }
    }
  }
}

/**
 * Implements _civicrm_managed().
 */
function civicontactsapp_civicrm_managed(&$entities) {
    $cca_sync_custom_key = getCCASyncCustomFieldKey();
    $entities[] = array(
        'module'  => 'au.com.agileware.civicontactsapp',
        'name'    => 'ccaautogroup',
        'entity'  => 'Group',
        'cleanup' => 'never',
        'params' => array(
            'version' => 3,
            'name'    => 'CiviCRM App Contacts',
            'title'   => 'CiviCRM App Contacts',
            'description' => "Contacts from CiviCRM application will be added in this group.",
            'source' => "au.com.agileware.civicontactsapp",
            'group_type' => "Access Control",
            'is_reserved' => 1,
            $cca_sync_custom_key => 1,
        ),
    );
}

function getCCASyncCustomFieldKey() {
    $customfield_result = civicrm_api3('CustomField', 'getsingle', array(
        'sequential' => 1,
        'return' => array("id"),
        'name' => "Sync_to_CCA",
    ));
    $cca_sync_custom_id = $customfield_result["id"];
    $cca_sync_custom_key = "custom_".$cca_sync_custom_id;

    return $cca_sync_custom_key;
}

/**
 * Implements hook_civicrm_post().
 */
function civicontactsapp_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if($objectName == "GroupContact") {
    $contactid = $objectRef[0];
    if($op == "delete" || $op == "create") {
      civicrm_api3('CCAGroupContactsLog', 'create', array(
        'groupid'   => $objectId,
        'contactid' => $contactid,
        'action'    => $op,
      ));
    }
  }

  if($objectName == "Group") {
    $cca_sync_custom_key = getCCASyncCustomFieldKey();

    $group_params = array(
      'sequential' => 1,
      'return'     => array("id", $cca_sync_custom_key),
      'id'         => $objectRef->id
    );
    $group_details = civicrm_api3('Group', 'getsingle', $group_params);

    $turnSyncOn = false;
    if(isset($group_details[$cca_sync_custom_key]) && sizeof($group_details[$cca_sync_custom_key]) > 0 && $group_details[$cca_sync_custom_key][0] == "1") {
      $turnSyncOn = true;
    }

    $group_last_log = civicrm_api3('CCAGroupsLog', 'get', array(
      'sequential' => 1,
      'groupid'    => $objectRef->id,
      'options' => array('limit' => 1, 'sort' => "id DESC"),
    ));

    $addNewLogEntry = true;
    if($group_last_log["count"] > 0) {
      $last_log = $group_last_log["values"][0];
      if($last_log["action"] == "on" && $turnSyncOn) {
        $addNewLogEntry = false;
      }

      if($last_log["action"] == "off" && !$turnSyncOn) {
        $addNewLogEntry = false;
      }
    }

    if($addNewLogEntry) {
      civicrm_api3('CCAGroupsLog', 'create', array(
        'groupid'  => $objectRef->id,
        'action'   => ($turnSyncOn) ? 'on' : 'off',
      ));
    }
  }
}
