<?php

use CRM_Civicontact_ExtensionUtil as E;

require_once 'lib/phpqrcode/qrlib.php';

class CRM_Civicontact_Page_GenerateQRCode extends CRM_Core_Page {

  public function run() {
    $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');
    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contactID;
    $contact->find(TRUE);

    $session = CRM_Core_Session::singleton();
    $isMe = ($contactID == $session->get('userID'));
    if (!$isMe) {
      throw new \Civi\API\Exception\UnauthorizedException('You\'re not authorized to view this page.');
    }

    // Checksum
    $hash = CRM_Civicontact_Utils_Authentication::getCCAHash($contactID);

    $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum($contact->id, NULL, 24, $hash);

    $qr_code_pay_load = [
      "contact_id" => $contactID,
      "checksum" => $cs,
      "site_key" => CIVICRM_SITE_KEY,
      "auth_url" => CRM_Utils_System::url(
        'civicrm/cca/auth',
        ['cid' => $contactID, 'cs' => $cs],
        TRUE,
        NULL,
        FALSE,
        TRUE
      ),
    ];
    \Civi::log()->info(print_r($qr_code_pay_load, TRUE));

    QRcode::png(json_encode($qr_code_pay_load), FALSE, QR_ECLEVEL_H, 5, 3);
    exit;
  }

}
