<?php

class CRM_Civicontact_Utils_Authentication {

  /**
   * The prefix for key name in cache table
   */
  public const HASH_PREFIX = 'CCA_HASH_CID_';

  /**
   * The key name in settings table
   */
  public const SETTINGS = 'cca_auth';

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

    $check = CRM_Contact_BAO_Contact_Utils::generateChecksum($contactID, $inputTS, $inputLF, $hash);

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
   * Drop API key for all users who are using the Mobile App
   */
  public static function invalidateAuthentication() {
    // drop API keys
    $settings = self::getSettings();
    foreach (array_keys($settings['users']) as $id) {
      $contact     = new CRM_Contact_BAO_Contact();
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
    $ip       = $_SERVER['REMOTE_ADDR'];
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
    $ids      = array_keys($settings['users']);
    if (isset($contactID) || in_array($contactID, $ids)) {
      $ids = [$contactID];
    }
    foreach ($ids as $id) {
      unset($settings['users'][$id]);
    }
    self::saveSettings($settings);
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