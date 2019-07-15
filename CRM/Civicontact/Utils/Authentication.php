<?php

class CRM_Civicontact_Utils_Authentication {

  public const HASH_PREFIX = 'CCA_HASH_CID_';

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
}