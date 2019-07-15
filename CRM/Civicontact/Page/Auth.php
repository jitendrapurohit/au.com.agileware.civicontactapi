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

    $key = new CRM_Civicontact_BAO_CCAKey();
    $key->contact_id = $contactID;
    $key->find(TRUE);

    // Validate checksum
    if (!$key->hash || !CRM_Civicontact_BAO_CCAKey::validChecksum($contactID, $cs, $key->hash)) {
      CRM_Utils_JSON::output([
        'error'   => 1,
        'message' => 'Failed to authenticate.',
      ]);
      exit();
    }

    $key->hash = NULL;
    $key->save();

    // Passed all validation
    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contactID;

    if(!$contact->find(TRUE)) {
      CRM_Core_Error::fatal(ts('Required cid parameter is invalid.'));
    }

    if(!$contact->api_key) {
      $api_key = md5($contact->id.rand(100000,999999).time());
      $contact->api_key = $api_key;
      $contact->save();
    }

    CRM_Utils_JSON::output([
      'api_key' => $contact->api_key,
    ]);
    exit();
  }
}
