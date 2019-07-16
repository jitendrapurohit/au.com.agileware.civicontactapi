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

    $hash = Civi::cache()
                ->get(CRM_Civicontact_Utils_Authentication::HASH_PREFIX . $contactID);

    // Validate checksum
    if (!$hash || !CRM_Civicontact_BAO_CCAKey::validChecksum($contactID, $cs, $hash)) {
      CRM_Utils_JSON::output([
        'error'   => 1,
        'message' => 'Failed to authenticate. Please generate a new QR code.',
      ]);
      exit();
    }

    Civi::cache()
        ->delete(CRM_Civicontact_Utils_Authentication::HASH_PREFIX . $contactID);

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

    CRM_Civicontact_Utils_Authentication::updateIP($contactID);

    CRM_Utils_JSON::output([
      'api_key' => $contact->api_key,
    ]);
    exit();
  }
}
