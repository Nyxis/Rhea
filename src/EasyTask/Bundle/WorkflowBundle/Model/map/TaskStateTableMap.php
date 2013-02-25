<?php

namespace EasyTask\Bundle\WorkflowBundle\Model\map;

use \RelationMap;
use \TableMap;

/**
 * This class defines the structure of the 'task_state' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator.src.EasyTask.Bundle.WorkflowBundle.Model.map
 */
class TaskStateTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'src.EasyTask.Bundle.WorkflowBundle.Model.map.TaskStateTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('task_state');
        $this->setPhpName('TaskState');
        $this->setClassname('EasyTask\\Bundle\\WorkflowBundle\\Model\\TaskState');
        $this->setPackage('src.EasyTask.Bundle.WorkflowBundle.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('task_id', 'TaskId', 'INTEGER', 'task', 'id', true, null, null);
        $this->addColumn('code', 'Code', 'VARCHAR', true, 255, null);
        $this->addColumn('next', 'Next', 'VARCHAR', true, 255, null);
        $this->addColumn('current', 'Current', 'BOOLEAN', true, 1, null);
        $this->addColumn('created_at', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('updated_at', 'UpdatedAt', 'TIMESTAMP', false, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Task', 'EasyTask\\Bundle\\WorkflowBundle\\Model\\Task', RelationMap::MANY_TO_ONE, array('task_id' => 'id', ), null, null);
    } // buildRelations()

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array Associative array (name => parameters) of behaviors
     */
    public function getBehaviors()
    {
        return array(
            'timestampable' =>  array (
  'create_column' => 'created_at',
  'update_column' => 'updated_at',
  'disable_updated_at' => 'false',
),
        );
    } // getBehaviors()

} // TaskStateTableMap
