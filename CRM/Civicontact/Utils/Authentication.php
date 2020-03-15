<?php

use CRM_Civicontact_ExtensionUtil as E;

class CRM_Civicontact_Utils_Authentication {

  /**
   * The prefix for key name in cache table
   */
  const HASH_PREFIX = 'CCA-HASH-CID-';

  /**
   * The key name in settings table
   */
  const SETTINGS = 'cca_auth';

  const ORIGINS
    = [
      'ionic://localhost',
      'http://localhost',
    ];

  /**
   * called by path civicrm/cca/email
   *
   * @throws \CRM_Core_Exception
   * @throws \CiviCRM_API3_Exception
   */
  public static function sendMail() {
    $id = CRM_Utils_Request::retrieve('id', 'Positive');
    $contact = civicrm_api3('Contact', 'get', [
      'id' => $id,
    ]);
    // no contact or multiple contacts should be skipped
    if ($contact['count'] !== 1) {
      CRM_Core_Session::setStatus(E::ts('Incorrect Contact ID.'), 'CiviContact', 'error');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', ['cid' => $id]));
    }
    $contact = array_shift($contact['values']);
    // no primary email - skip
    if (!$contact['email']) {
      CRM_Core_Session::setStatus(E::ts('There is no email address set for this Contact.'), 'CiviContact', 'error');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', ['cid' => $id]));
    }
    $email = [];
    $from = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => "from_email_address",
      'is_active' => 1,
      'options' => [
        'limit' => 0,
      ],
    ]);
    // no from address - skip
    if (!$from['count']) {
      CRM_Core_Session::setStatus(E::ts('CiviCRM has no From Email Address set. Please set this option and try again.'), 'CiviContact', 'error');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', ['cid' => $id]));
    }
    $from = array_shift($from['values']);
    $email['from'] = $from['label'];
    // message body
    $template = CRM_Core_Smarty::singleton();
    $template->assign('auth_url', self::generateAuthURL($id));
    // more variables
    $email['html'] = $template->fetch('string:' . file_get_contents(E::path('templates/CRM/Civicontact/email.tpl')));

    $email['toName'] = $contact['display_name'];
    $email['toEmail'] = $contact['email'];
    $email['subject'] = E::ts('CiviContact set up instructions');

    if (CRM_Utils_Mail::send($email)) {
      CRM_Core_Session::setStatus(E::ts('CiviContact authentication email sent.'), 'CiviContact', 'success');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', ['cid' => $id]));
    }
    else {
      CRM_Core_Session::setStatus(E::ts('Unknown error - CiviContact authentication email not sent.'), 'CiviContact', 'error');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', ['cid' => $id]));

    }
  }

  /**
   * Generate a hash string
   *
   * @return string
   */
  public static function generate_hash() {
    return md5(uniqid(rand(), TRUE));
  }

  /**
   * Make sure the checksum is valid for the passed in contactID.
   *
   * @param int $contactID
   * @param string $inputCheck
   *   Checksum to match against.
   * @param string $hash
   *   Contact hash, if sent, prevents a query in inner loop.
   *
   * @return bool
   *   true if valid, else false
   */
  public static function validChecksum($contactID, $inputCheck, $hash = NULL) {

    $input = CRM_Utils_System::explode('_', $inputCheck, 3);

    $inputCS = CRM_Utils_Array::value(0, $input);
    $inputTS = CRM_Utils_Array::value(1, $input);
    $inputLF = CRM_Utils_Array::value(2, $input);

    $check = CRM_Contact_BAO_Contact_Utils::generateChecksum(
      $contactID,
      $inputTS,
      $inputLF,
      $hash
    );

    if (!hash_equals($check, $inputCheck)) {
      return FALSE;
    }

    // no life limit for checksum
    if ($inputLF == 'inf') {
      return TRUE;
    }

    // checksum matches so now check timestamp
    $now = time();
    return ($inputTS + ($inputLF * 60 * 60) >= $now);
  }

  /**
   * Wrap of the core's function
   * the live time by default is set by the settings
   *
   * @param $entityId
   * @param null $ts
   * @param null $live number of hours or null to get it from the settings
   * @param null $hash the cca hash by default
   * @param string $entityType
   * @param null $hashSize
   *
   * @return array
   */
  public static function generateChecksum(
    $entityId,
    $ts = NULL,
    $live = NULL,
    $hash = NULL,
    $entityType = 'contact',
    $hashSize = NULL) {
    // default live time
    if (!$live) {
      try {
        $timeout = civicrm_api3('Setting', 'get', [
          'sequential' => 1,
          'return' => ["cca_checksum_timeout"],
        ]);
        $timeout = array_shift($timeout['values'])['cca_checksum_timeout'];
      } catch (CiviCRM_API3_Exception $e) {
        $timeout = 1;
      }
      $days = 24 * $timeout;
      $live = $days > 0 ? $days : 24;
    }
    // default hash
    if (!$hash && $entityType == 'contact') {
      self::getCCAHash($entityId);
    }

    return CRM_Contact_BAO_Contact_Utils::generateChecksum($entityId, $ts, $live, $hash, $entityType, $hashSize);
  }

  /**
   * Drop API key for all users who are using the Mobile App
   */
  public static function invalidateAuthentication() {
    // drop API keys
    $settings = self::getSettings();
    foreach (array_keys($settings['users']) as $id => $info) {
      $contact = new CRM_Contact_BAO_Contact();
      $contact->id = $id;
      $contact->find(TRUE);
      $contact->api_key = NULL;
      $contact->save();
      self::deleteUserInRecord($id);
    }
  }

  /**
   * Get the stored settings
   *
   * @return array|mixed
   */
  public static function getSettings() {
    $settings = Civi::settings()->get(self::SETTINGS);
    if (!$settings) {
      $settings = [
        'users' => [],
      ];
    }

    return $settings;
  }

  /**
   * Update the login IP address for the given user
   *
   * @param string $contactID
   */
  public static function updateIP($contactID) {
    $settings = self::getSettings();
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!$settings['users'][$contactID]) {
      $settings['users'][$contactID] = [];
    }
    $settings['users'][$contactID]['ip_address'] = $ip;
    self::saveSettings($settings);
  }

  /**
   * Delete the user record in settings
   * Will delete all user records if contact ID not supplied
   *
   * @param string $contactID
   */
  public static function deleteUserInRecord($contactID = NULL) {
    $settings = self::getSettings();
    $ids = array_keys($settings['users']);
    if (isset($contactID) || in_array($contactID, $ids)) {
      $ids = [$contactID];
    }
    foreach ($ids as $id) {
      unset($settings['users'][$id]);
    }
    self::saveSettings($settings);
  }

  /**
   * Generate the authentication URL for the given contact
   *
   * @param int $contactId
   *
   * @return string
   *               The url
   * @throws \Exception
   */
  public static function generateAuthURL($contactId) {
    if (!$contactId) {
      CRM_Core_Error::fatal(
        ts('Required cid parameter invalid or not provided.')
      );
      return "";
    }

    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contactId;

    if (!$contact->find(TRUE)) {
      CRM_Core_Error::fatal(ts('Required cid parameter is invalid.'));
      return "";
    }

    if (!$contact->api_key) {
      $api_key = md5($contact->id . rand(100000, 999999) . time());
      $contact->api_key = $api_key;
      $contact->save();
    }

    // Checksum
    $cs = self::generateChecksum($contact->id);

    $url = "https://civicontact.com.au/login?auth=" .
      urlencode(
        CRM_Utils_System::url(
          'civicrm/cca/auth',
          ['cid' => $contactId, 'cs' => $cs],
          TRUE,
          NULL,
          FALSE,
          TRUE
        )
      );
    return $url;
  }

  /**
   * The hash key used by cca checksum
   *
   * @param $contactID
   *
   * @return mixed|string
   */
  public static function getCCAHash($contactID) {
    $settings = self::getSettings();
    if (!$settings['users'][$contactID]) {
      $settings['users'][$contactID] = [];
    }
    $hash = $settings['users'][$contactID]['hash'];
    if (!$hash) {
      $hash = self::generate_hash();
      $settings['users'][$contactID]['hash'] = $hash;
      self::saveSettings($settings);
    }
    return $hash;
  }

  public static function addCORSHeader() {
    foreach ($_SERVER as $key => $value) {
      if ($key == 'HTTP_ORIGIN') {
        if (in_array($value, self::ORIGINS)) {
          header('Access-Control-Allow-Origin: ' . $value);
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Save the settings
   *
   * @param $settings
   */
  private static function saveSettings($settings) {
    if (!$settings['users'] || !is_array($settings['users'])) {
      Civi::log()->error('CCA: Wrong settings array');
    }
    Civi::settings()->set(self::SETTINGS, $settings);
  }
}
