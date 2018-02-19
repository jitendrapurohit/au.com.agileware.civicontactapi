DROP TABLE IF EXISTS civicrm_cca_group_contacts_log;

DROP TABLE IF EXISTS civicrm_cca_groups_log;

DROP TABLE IF EXISTS civicrm_cca_group_settings;

DELETE FROM civicrm_custom_group WHERE name = 'CCA_Group_Settings';

DELETE  FROM civicrm_option_group WHERE name = 'sync_to_cca';

DELETE  FROM civicrm_group WHERE name = 'CiviCRM App Contacts';