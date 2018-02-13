<?php
use CRM_Civicontactsapp_ExtensionUtil as E;
require_once 'lib/phpqrcode/qrlib.php';

class CRM_Civicontactsapp_Page_GenerateQRCode extends CRM_Core_Page {

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

    $config = CRM_Core_Config::singleton();
    $restpath = $config->resourceBase . 'extern/rest.php';
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain_name = $protocol.$_SERVER['SERVER_NAME'];
    $restendpoint = $domain_name.$restpath;

    $group = civicrm_api3("Group","get",array(
       "name" => "CiviCRM App Contacts",
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
      "api_key"                   => $contact->api_key,
      "site_key"                  => CIVICRM_SITE_KEY,
      "rest_end_point"            => $restendpoint,
      "groupid"                   => $groupid,
      "domain_name"               => $domain_name,
      "licence_code"              => $licence_code,
    );
    
    QRcode::png(json_encode($qr_code_pay_load), FALSE, QR_ECLEVEL_H, 5, 3);
    exit;
  }

}
