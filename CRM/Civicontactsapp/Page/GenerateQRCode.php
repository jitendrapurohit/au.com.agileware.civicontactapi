<?php
use CRM_Civicontactsapp_ExtensionUtil as E;
require_once 'lib/phpqrcode/qrlib.php';

class CRM_Civicontactsapp_Page_GenerateQRCode extends CRM_Core_Page {

  public function run() {
    $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');
    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contactID;
    $contact->find(TRUE);

    $qr_code_pay_load = array(
      "contact_id"     => $contactID,
      "api_key"        => $contact->api_key,
      "site_key"       => CIVICRM_SITE_KEY,
      "rest_end_point" => CRM_Utils_System::url('civicrm/ajax/rest', '', TRUE),
    );
    
    QRcode::png(json_encode($qr_code_pay_load), FALSE, QR_ECLEVEL_H, 5, 3);
    exit;
  }

}
