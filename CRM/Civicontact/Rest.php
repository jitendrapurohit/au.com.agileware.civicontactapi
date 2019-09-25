<?php

class CRM_Civicontact_Rest {

  public static function api() {
    CRM_Civicontact_Utils_Authentication::addCORSHeader();
    if (defined('PANTHEON_ENVIRONMENT')) {
      ini_set('session.save_handler', 'files');
    }
    $rest = new CRM_Utils_REST();

    // Json-appropriate header will be set by CRM_Utils_Rest
    // But we need to set header here for non-json
    if (empty($_GET['json'])) {
      header('Content-Type: text/xml');
    }
    echo $rest->bootAndRun();
    die();
  }
}