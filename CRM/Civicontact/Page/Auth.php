<?php

use CRM_Civicontact_ExtensionUtil as E;

class CRM_Civicontact_Page_Auth extends CRM_Core_Page {

  public function run() {
    // Get info from url parameters
    try {
      $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');
      $cs        = CRM_Utils_Request::retrieve('cs', 'Text');
    } catch (CRM_Core_Exception $e) {
      http_response_code(400);
      CRM_Utils_JSON::output([
        'error'   => 1,
        'message' => 'Parameters missing.',
      ]);
      exit();
    }

    $hash = Civi::cache('long')
                ->get(CRM_Civicontact_Utils_Authentication::HASH_PREFIX . $contactID);

    // Validate checksum
    if (!$hash || !CRM_Civicontact_Utils_Authentication::validChecksum($contactID, $cs, $hash)) {
      CRM_Utils_JSON::output([
        'error'   => 1,
        'message' => 'Failed to authenticate. Please generate a new QR code.',
      ]);
      exit();
    }

    Civi::cache('long')
        ->delete(CRM_Civicontact_Utils_Authentication::HASH_PREFIX . $contactID);

    // Passed all validation
    $contact     = new CRM_Contact_BAO_Contact();
    $contact->id = $contactID;

    if (!$contact->find(TRUE)) {
      CRM_Core_Error::fatal(ts('Required cid parameter is invalid.'));
    }

    if (!$contact->api_key) {
      $api_key          = md5($contact->id . rand(100000, 999999) . time());
      $contact->api_key = $api_key;
      $contact->save();
    }

    CRM_Civicontact_Utils_Authentication::updateIP($contactID);

    // Reset endpoint
    $config = CRM_Core_Config::singleton();
    $restpath = $config->resourceBase . 'extern/rest.php';
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    if (strpos($restpath, $protocol) !== FALSE) {
      $restendpoint = $restpath;
    } else {
      $domain_name = $protocol.$_SERVER['SERVER_NAME'];
      $restendpoint = $domain_name.$restpath;
    }

    // Group id
    $group = civicrm_api3("Group","get",array(
      "name" => "CiviContact",
      "sequential" => true
    ));

    $groupid = 0;
    if($group["count"] > 0) {
      $groupid = $group["values"][0]["id"];
    }

    // Licence code
    $licence_code = Civi::settings()->get('cca_licence_code');

    CRM_Utils_JSON::output(
      [
        'error'          => 0,
        "contact_id"     => $contactID,
        'api_key'        => $contact->api_key,
        "contact_name"   => $contact->display_name,
        "site_key"       => CIVICRM_SITE_KEY,
        "rest_end_point" => $restendpoint,
        "groupid"        => $groupid,
        "domain_name"    => $_SERVER['SERVER_NAME'],
        "licence_code"   => $licence_code,
      ]
    );
    exit();
  }
}
