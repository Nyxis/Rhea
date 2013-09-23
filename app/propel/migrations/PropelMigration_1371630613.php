<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1371630613.
 * Generated on 2013-06-19 10:30:13 by qcerny
 */
class PropelMigration_1371630613
{

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'default' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `internal_type_i18n`;

DROP TABLE IF EXISTS `task`;

DROP TABLE IF EXISTS `task_state`;

RENAME TABLE `internal_type` TO `group`;

DROP INDEX `internal_FI_1` ON `internal`;

ALTER TABLE `internal` CHANGE `internal_type_id` `group_id` INTEGER NOT NULL;

CREATE INDEX `internal_FI_1` ON `internal` (`group_id`);

CREATE TABLE `group_credential`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `group_id` INTEGER NOT NULL,
    `credential_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `group_credential_FI_1` (`group_id`),
    INDEX `group_credential_FI_2` (`credential_id`)
) ENGINE=MyISAM;

CREATE TABLE `person_credential`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `person_id` INTEGER NOT NULL,
    `credential_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `person_credential_FI_1` (`person_id`),
    INDEX `person_credential_FI_2` (`credential_id`)
) ENGINE=MyISAM;

CREATE TABLE `credential`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `group_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT \'fr\' NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`,`locale`)
) ENGINE=MyISAM;

CREATE TABLE `credential_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT \'fr\' NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`,`locale`)
) ENGINE=MyISAM;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'default' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `group_credential`;

DROP TABLE IF EXISTS `person_credential`;

DROP TABLE IF EXISTS `credential`;

DROP TABLE IF EXISTS `group_i18n`;

DROP TABLE IF EXISTS `credential_i18n`;

RENAME TABLE `group` TO `internal_type`;

DROP INDEX `internal_FI_1` ON `internal`;

ALTER TABLE `internal` CHANGE `group_id` `internal_type_id` INTEGER NOT NULL;

CREATE INDEX `internal_FI_1` ON `internal` (`internal_type_id`);

CREATE TABLE `internal_type_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT \'fr\' NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`,`locale`)
) ENGINE=MyISAM;

CREATE TABLE `task`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(255) NOT NULL,
    `created_by` VARCHAR(255) NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `task_I_1` (`type`(255))
) ENGINE=InnoDB;

CREATE TABLE `task_state`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `task_id` INTEGER NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `assigned_to` VARCHAR(255) NOT NULL,
    `next` VARCHAR(255) NOT NULL,
    `current` TINYINT(1) NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `task_state_FI_1` (`task_id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
