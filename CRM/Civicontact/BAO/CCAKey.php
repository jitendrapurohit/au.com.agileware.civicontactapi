<?php
use CRM_Civicontact_ExtensionUtil as E;

class CRM_Civicontact_BAO_CCAKey extends CRM_Civicontact_DAO_CCAKey {

  /**
   * Create a new CCAKey based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Civicontact_DAO_CCAKey|NULL
   *
  public static function create($params) {
    $className = 'CRM_Civicontact_DAO_CCAKey';
    $entityName = 'CCAKey';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

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
   * @param int $life
   *  hour
   *
   * @return bool
   */
  public function validateHash($life) {
    $now = time();
    return ($this->date + ($life * 60 * 60) >= $now);
  }

  public function generateHash() {
    $this->hash = md5(uniqid(rand(), TRUE));
  }
}
