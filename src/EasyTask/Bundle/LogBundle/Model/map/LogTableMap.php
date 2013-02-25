<?php

namespace EasyTask\Bundle\LogBundle\Model\map;

use \RelationMap;
use \TableMap;

/**
 * This class defines the structure of the 'log' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator.src.EasyTask.Bundle.LogBundle.Model.map
 */
class LogTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'src.EasyTask.Bundle.LogBundle.Model.map.LogTableMap';

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
        $this->setName('log');
        $this->setPhpName('Log');
        $this->setClassname('EasyTask\\Bundle\\LogBundle\\Model\\Log');
        $this->setPackage('src.EasyTask.Bundle.LogBundle.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('code', 'Code', 'VARCHAR', true, 255, null);
        $this->addColumn('message', 'Message', 'LONGVARCHAR', true, null, null);
        $this->addColumn('tags', 'Tags', 'LONGVARCHAR', true, null, null);
        $this->addColumn('date', 'Date', 'TIMESTAMP', true, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
    } // buildRelations()

} // LogTableMap
