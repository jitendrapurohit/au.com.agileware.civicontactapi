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
    if(!$isMe) {
      throw new \Civi\API\Exception\UnauthorizedException('You\'re not authorized to view this page.');
    }

    // Checksum
    $key = new CRM_Civicontact_BAO_CCAKey();
    $key->contact_id = $contactID;
    $key->find(TRUE);
    if (!$key->hash || $key->validateHash(24)) {
      $key->generateHash();
      $key->save();
      $key->find(TRUE);
    }
    \Civi::log()->info($key->hash);
    $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum($contact->id, strtotime($key->date), 24, $key->hash);

    $config = CRM_Core_Config::singleton();
    $restpath = $config->resourceBase . 'extern/rest.php';
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    if (strpos($restpath, $protocol) !== FALSE) {
      $restendpoint = $restpath;
    } else {
      $domain_name = $protocol.$_SERVER['SERVER_NAME'];
      $restendpoint = $domain_name.$restpath;
    }

    $group = civicrm_api3("Group","get",array(
       "name" => "CiviContact",
       "sequential" => true
    ));

    $groupid = 0;
    if($group["count"] > 0) {
        $groupid = $group["values"][0]["id"];
    }

    $licence_code = Civi::settings()->get('cca_licence_code');
    $qr_code_pay_load = array(
      "contact_id"                => $contactID,
      "contact_name"              => $contact->display_name,
//      "api_key"                   => $contact->api_key,
      "checksum"                  => $cs,
      "site_key"                  => CIVICRM_SITE_KEY,
      "rest_end_point"            => $restendpoint,
      "groupid"                   => $groupid,
      "domain_name"               => $_SERVER['SERVER_NAME'],
      "licence_code"              => $licence_code,
    );
    \Civi::log()->info(print_r($qr_code_pay_load, TRUE));

    QRcode::png(json_encode($qr_code_pay_load), FALSE, QR_ECLEVEL_H, 5, 3);
    exit;
  }

}
