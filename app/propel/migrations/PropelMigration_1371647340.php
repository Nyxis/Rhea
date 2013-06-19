<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1371647340.
 * Generated on 2013-06-19 15:09:00 by qcerny
 */
class PropelMigration_1371647340
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

DROP INDEX `consultant_FI_2` ON `consultant`;

ALTER TABLE `consultant` CHANGE `internal_id` `group_id` INTEGER NOT NULL;

ALTER TABLE `consultant`
    ADD `password` VARCHAR(255) NOT NULL AFTER `crh_id`,
    ADD `email` VARCHAR(255) NOT NULL AFTER `group_id`,
    ADD `firstname` VARCHAR(255) NOT NULL AFTER `email`,
    ADD `lastname` VARCHAR(255) NOT NULL AFTER `firstname`,
    ADD `telephone` VARCHAR(255) AFTER `lastname`,
    ADD `mobile` VARCHAR(255) AFTER `telephone`,
    ADD `created_at` DATETIME AFTER `mobile`,
    ADD `updated_at` DATETIME AFTER `created_at`,
    ADD `tree_left` INTEGER AFTER `updated_at`,
    ADD `tree_right` INTEGER AFTER `tree_left`,
    ADD `tree_level` INTEGER AFTER `tree_right`,
    ADD `tree_scope` INTEGER AFTER `tree_level`;

CREATE INDEX `consultant_I_2` ON `consultant` (`group_id`);

CREATE UNIQUE INDEX `consultant_U_1` ON `consultant` (`email`);

ALTER TABLE `internal`
    ADD `descendant_class` VARCHAR(100) AFTER `tree_scope`;

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

DROP INDEX `consultant_I_2` ON `consultant`;

DROP INDEX `consultant_U_1` ON `consultant`;

ALTER TABLE `consultant` CHANGE `group_id` `internal_id` INTEGER NOT NULL;

ALTER TABLE `consultant` DROP `password`;

ALTER TABLE `consultant` DROP `email`;

ALTER TABLE `consultant` DROP `firstname`;

ALTER TABLE `consultant` DROP `lastname`;

ALTER TABLE `consultant` DROP `telephone`;

ALTER TABLE `consultant` DROP `mobile`;

ALTER TABLE `consultant` DROP `created_at`;

ALTER TABLE `consultant` DROP `updated_at`;

ALTER TABLE `consultant` DROP `tree_left`;

ALTER TABLE `consultant` DROP `tree_right`;

ALTER TABLE `consultant` DROP `tree_level`;

ALTER TABLE `consultant` DROP `tree_scope`;

CREATE INDEX `consultant_FI_2` ON `consultant` (`internal_id`);

ALTER TABLE `internal` DROP `descendant_class`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
