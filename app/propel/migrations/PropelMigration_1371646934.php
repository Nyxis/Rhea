<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1371646934.
 * Generated on 2013-06-19 15:02:14 by qcerny
 */
class PropelMigration_1371646934
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

CREATE UNIQUE INDEX `internal_U_1` ON `internal` (`email`);

CREATE UNIQUE INDEX `person_U_1` ON `person` (`email`);

DROP INDEX `workflow_FI_1` ON `workflow`;

DROP INDEX `workflow_node_FI_4` ON `workflow_node`;

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

DROP INDEX `internal_U_1` ON `internal`;

DROP INDEX `person_U_1` ON `person`;

CREATE INDEX `workflow_FI_1` ON `workflow` (`created_by`);

CREATE INDEX `workflow_node_FI_4` ON `workflow_node` (`assigned_to`);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
