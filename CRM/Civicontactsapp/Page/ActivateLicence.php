<?php
use CRM_Civicontactsapp_ExtensionUtil as E;

class CRM_Civicontactsapp_Page_ActivateLicence extends CRM_Core_Page {

  public function run() {
      $licence_code = Civi::settings()->get('cca_licence_code');
      $licenceVerification = $this->verifyLicence($licence_code);
      $licenceVerification = simplexml_load_string($licenceVerification);
      if($licenceVerification->status == "Invalid") {
        CRM_Core_Session::setStatus(ts('You have entered invalid or unauthorized licence code. Please try again or contact Agileware for more details.'), ts('Licence Code Activation'), 'error');
      } else {
        CRM_Core_Session::setStatus(ts('You have successfully activated licence code following domains.<br><br>'.$licenceVerification->validdomain.''), ts('Licence Code Activation'), 'success');
        Civi::settings()->set('cca_licence_activated', 1);
      }
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/cca/settings'));
  }

  private function verifyLicence($licence_code) {
      $curl = curl_init();
      $domain_name = $_SERVER['SERVER_NAME'];
      curl_setopt_array($curl, array(
          CURLOPT_URL => "http://whmcs.launchpad.agileware.com.au/modules/servers/licensing/verify.php",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "licensekey=".$licence_code."&domain=".$domain_name,
      ));
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);

      if ($err) {
          return FALSE;
      } else {
          $response = "<licence>".$response;
          $response = $response."</licence>";
          return $response;
      }
  }
}