<?php
use CRM_Civicontactsapp_ExtensionUtil as E;
require_once 'lib/phpqrcode/qrlib.php';

class CRM_Civicontactsapp_Page_GenerateQRCode extends CRM_Core_Page {

  public function run() {
    $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');
    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contactID;
    $contact->find(TRUE);

    $activityDeviceID = CRM_Core_BAO_CustomField::getCustomFieldID("Device_ID", "CCA_Activity_Fields");
    $contactDeviceID = CRM_Core_BAO_CustomField::getCustomFieldID("Device_ID", "CCA_Contact_Fields");
    $relationshipDeviceID = CRM_Core_BAO_CustomField::getCustomFieldID("Device_ID", "CCA_Relationship_fields");
    $relationshipCreatedDate = CRM_Core_BAO_CustomField::getCustomFieldID("Created_Date", "CCA_Relationship_fields");

    $qr_code_pay_load = array(
      "contact_id"                => $contactID,
      "api_key"                   => $contact->api_key,
      "site_key"                  => CIVICRM_SITE_KEY,
      "rest_end_point"            => CRM_Utils_System::url('civicrm/ajax/rest', '', TRUE),
      "activity_device_id"        => "cusomt_".$activityDeviceID,
      "contact_device_id"         => "cusomt_".$activityDeviceID,
      "relationship_device_id"    => "cusomt_".$activityDeviceID,
      "relationship_created_date" => "cusomt_".$activityDeviceID,
    );

    QRcode::png(json_encode($qr_code_pay_load), FALSE, QR_ECLEVEL_H, 5, 3);
    exit;
  }

}
