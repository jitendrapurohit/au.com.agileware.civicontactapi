<?php
use CRM_Civicontactsapp_ExtensionUtil as E;

class CRM_Civicontactsapp_Page_QRCode extends CRM_Core_Page {

  public function run() {
    $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');
    if (!$contactID) {
      CRM_Core_Error::fatal(ts('Required cid parameter invalid or not provided.'));
    }

    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contactID;

    if(!$contact->find(TRUE)) {
      CRM_Core_Error::fatal(ts('Required cid parameter is invalid.'));
    }

    $session = CRM_Core_Session::singleton();
    $isMe = ($contactID == $session->get('userID'));
    if(!$isMe) {
      throw new \Civi\API\Exception\UnauthorizedException('You\'re not authorized to view this page.');
    }

    if(!$contact->api_key) {
      $api_key = md5($contact->id.rand(100000,999999).time());
      $contact->api_key = $api_key;
      $contact->save();
    }

    $title = E::ts('QR Code of ')." ".$contact->display_name;
    CRM_Utils_System::setTitle($title);

    $this->assign('qrcode', $url = CRM_Utils_System::url( 'civicrm/contact/generate/qrcode', "cid=$contactID"));
    $this->assign('title', $title);
    parent::run();
  }

}
