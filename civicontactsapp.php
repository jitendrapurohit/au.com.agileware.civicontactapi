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
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civicontactsapp_civicrm_managed(&$entities) {
  _civicontactsapp_civix_civicrm_managed($entities);
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
      $contactID = $context["contact_id"];
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
