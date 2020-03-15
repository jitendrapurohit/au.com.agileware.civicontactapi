<?php

use CRM_Civicontact_ExtensionUtil as E;

class CRM_Civicontact_Page_QRCode extends CRM_Core_Page {

  public function run() {
    $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');
    if (!$contactID) {
      CRM_Core_Error::fatal(ts('Required cid parameter invalid or not provided.'));
    }

    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contactID;

    if (!$contact->find(TRUE)) {
      CRM_Core_Error::fatal(ts('Required cid parameter is invalid.'));
    }

    $session = CRM_Core_Session::singleton();
    $isMe = ($contactID == $session->get('userID'));
    if (!$isMe) {
      throw new \Civi\API\Exception\UnauthorizedException('You\'re not authorized to view this page.');
    }

    if (!$contact->api_key) {
      $api_key = md5($contact->id . rand(100000, 999999) . time());
      $contact->api_key = $api_key;
      $contact->save();
    }

    // Checksum
    $hash = CRM_Civicontact_Utils_Authentication::getCCAHash($contactID);

    $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum($contact->id, NULL, 24, $hash);

    $url = "https://civicontact.agileware.com.au?auth=" .
      urlencode(CRM_Utils_System::url(
        'civicrm/cca/auth',
        ['cid' => $contactID, 'cs' => $cs],
        TRUE,
        NULL,
        FALSE,
        TRUE
      ));

    \Civi::log()->info(print_r($url, TRUE));

    $title = E::ts('QR Code of ') . " " . $contact->display_name;
    CRM_Utils_System::setTitle($title);

    $this->assign('qrcode', $url = CRM_Utils_System::url('civicrm/contact/generate/qrcode', "cid=$contactID"));
    $this->assign('login_url', $url);
    $this->assign('title', $title);
    parent::run();
  }

}
