CREATE TABLE IF NOT EXISTS `civicrm_cca_group_contacts_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `groupid` INT UNSIGNED COMMENT 'FK to Group',
  `contactid` INT UNSIGNED COMMENT 'FK to Contact',
  `action` VARCHAR(10) NOT NULL,
  `createdat` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_cca_group_log_contact_id FOREIGN KEY (`contactid`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_cca_group_log_group_id FOREIGN KEY (`groupid`) REFERENCES `civicrm_group`(`id`) ON DELETE CASCADE
);
