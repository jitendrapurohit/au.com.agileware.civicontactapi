<?php

use CRM_Civicontact_ExtensionUtil as E;

class CRM_Civicontact_Page_Auth extends CRM_Core_Page {

  public function run() {
    // Get info from url parameters
    try {
      $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');
      $cs = CRM_Utils_Request::retrieve('cs', 'Text');
    } catch (CRM_Core_Exception $e) {
      http_response_code(400);
      CRM_Utils_JSON::output(
        [
          'error' => 1,
          'message' => 'Parameters missing.',
        ]
      );
      exit();
    }
    CRM_Civicontact_Utils_Authentication::addCORSHeader();

    $hash = CRM_Civicontact_Utils_Authentication::getCCAHash($contactID);

    // Validate checksum
    if (!$hash
      || !CRM_Civicontact_Utils_Authentication::validChecksum(
        $contactID,
        $cs,
        $hash
      )
    ) {
      CRM_Utils_JSON::output(
        [
          'error' => 1,
          'message' => 'Failed to authenticate. Please generate a new QR code.',
        ]
      );
      exit();
    }

    // invalidate the checksum
    CRM_Civicontact_Utils_Authentication::unsetCCAHash($contactID);

    // Passed all validation
    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contactID;

    if (!$contact->find(TRUE)) {
      CRM_Core_Error::fatal(ts('Required cid parameter is invalid.'));
    }

    if (!$contact->api_key) {
      $api_key = md5($contact->id . rand(100000, 999999) . time());
      $contact->api_key = $api_key;
      $contact->save();
    }

    CRM_Civicontact_Utils_Authentication::updateIP($contactID);

    // Reset endpoint
    $restendpoint = CRM_Utils_System::url(
      'civicrm/cca/rest',
      NULL,
      TRUE,
      NULL,
      FALSE,
      TRUE
    );

    // Group id
    $group = civicrm_api3(
      "Group",
      "get",
      [
        "name" => "CiviContact",
        "sequential" => TRUE,
      ]
    );

    $groupid = 0;
    if ($group["count"] > 0) {
      $groupid = $group["values"][0]["id"];
    }

    // Get Google analytics settings
    $gaResult = civicrm_api3('Setting', 'get', [
      'sequential' => 1,
      'return' => ["cca_client_google_analytics"],
    ]);
    $gaResult = array_shift($gaResult['values'])['cca_client_google_analytics'];

    CRM_Utils_JSON::output(
      [
        'error' => 0,
        "contact_id" => $contactID,
        'api_key' => $contact->api_key,
        "contact_name" => $contact->display_name,
        "site_key" => CIVICRM_SITE_KEY,
        "rest_end_point" => $restendpoint,
        "groupid" => $groupid,
        "domain_name" => $_SERVER['SERVER_NAME'],
        'cca_client_google_analytics' => $gaResult,
      ]
    );
    exit();
  }
}
