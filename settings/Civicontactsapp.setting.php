<?php

return array(
  'cca_global_config' => array(
    'group_name' => 'Contact App Settings',
    'group' => 'cca',
    'name' => 'cca_global_config',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'html_type' => 'Text',
    'description' => 'Enable Global Config',
    'title' =>  'Enable Global Config',
    'help_text' => 'If set it as Yes, Application config will be same for all users.',
  ),
  'cca_email_to_activity' => array(
    'group_name' => 'Contact App Settings',
    'group' => 'cca',
    'name' => 'cca_email_to_activity',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'html_type' => 'Text',
    'description' => 'Email to Activity',
    'title' =>  'Email to Activity',
    'help_text' => 'If set it as Yes, A new activity will be created for each email sent from Application.',
  ),
  'cca_sync_interval' => array(
    'group_name' => 'Contact App Settings',
    'group' => 'cca',
    'name' => 'cca_sync_interval',
    'type' => 'String',
    'add' => '4.7',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'is_domain' => 1,
    'is_contact' => 0,
    'default' => 'default',
    'description' => 'Sync Interval',
    'title' =>  'Sync Interval',
    'pseudoconstant' => array(
      'callback' => 'CRM_Civicontactsapp_Form_Settings::getSyncIntervalOptions',
    ),
    'html_attributes' => array(
      
    ),
    'help_text' => ''
  ),
 );
