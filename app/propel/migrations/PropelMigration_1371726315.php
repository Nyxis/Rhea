<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1371726315.
 * Generated on 2013-06-20 13:05:15 by qcerny
 */
class PropelMigration_1371726315
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

DROP INDEX `consultant_I_2` ON `consultant`;

CREATE INDEX `consultant_I_2` ON `consultant` (``);

CREATE INDEX `consultant_I_3` ON `consultant` (`group_id`);

CREATE INDEX `I_referenced_task_FK_1_1` ON `internal` (``);

ALTER TABLE `workflow` DROP `created_by`;

ALTER TABLE `workflow_node`
    ADD `ended` TINYINT(1) DEFAULT 0 AFTER `current`;

ALTER TABLE `workflow_node` DROP `assigned_to`;

CREATE TABLE `task`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT \'id, primary key\',
    `activation_date` DATETIME COMMENT \'date on task have to be completed\',
    `user_target_id` INTEGER COMMENT \'user whos concerned by the task\',
    `data` TEXT COMMENT \'json extra data for task\',
    `assigned_to` INTEGER NOT NULL COMMENT \'who have to handle this task\',
    `workflow_created_by` INTEGER COMMENT \'user who create workflow\',
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `task_FI_1` (`assigned_to`),
    INDEX `task_FI_3` (`workflow_created_by`)
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

DROP TABLE IF EXISTS `task`;

DROP INDEX `consultant_I_3` ON `consultant`;

DROP INDEX `consultant_I_2` ON `consultant`;

CREATE INDEX `consultant_I_2` ON `consultant` (`group_id`);

DROP INDEX `I_referenced_task_FK_1_1` ON `internal`;

ALTER TABLE `workflow`
    ADD `created_by` INTEGER NOT NULL AFTER `description`;

ALTER TABLE `workflow_node`
    ADD `assigned_to` INTEGER NOT NULL AFTER `name`;

ALTER TABLE `workflow_node` DROP `ended`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
