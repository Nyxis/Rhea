<?php

namespace EasyTask\Bundle\WorkflowBundle\Model\om;

use \Criteria;
use \Exception;
use \ModelCriteria;
use \ModelJoin;
use \PDO;
use \Propel;
use \PropelCollection;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use EasyTask\Bundle\WorkflowBundle\Model\Task;
use EasyTask\Bundle\WorkflowBundle\Model\TaskState;
use EasyTask\Bundle\WorkflowBundle\Model\TaskStatePeer;
use EasyTask\Bundle\WorkflowBundle\Model\TaskStateQuery;

/**
 * @method TaskStateQuery orderById($order = Criteria::ASC) Order by the id column
 * @method TaskStateQuery orderByTaskId($order = Criteria::ASC) Order by the task_id column
 * @method TaskStateQuery orderByCode($order = Criteria::ASC) Order by the code column
 * @method TaskStateQuery orderByNext($order = Criteria::ASC) Order by the next column
 * @method TaskStateQuery orderByCurrent($order = Criteria::ASC) Order by the current column
 * @method TaskStateQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method TaskStateQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method TaskStateQuery groupById() Group by the id column
 * @method TaskStateQuery groupByTaskId() Group by the task_id column
 * @method TaskStateQuery groupByCode() Group by the code column
 * @method TaskStateQuery groupByNext() Group by the next column
 * @method TaskStateQuery groupByCurrent() Group by the current column
 * @method TaskStateQuery groupByCreatedAt() Group by the created_at column
 * @method TaskStateQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method TaskStateQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method TaskStateQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method TaskStateQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method TaskStateQuery leftJoinTask($relationAlias = null) Adds a LEFT JOIN clause to the query using the Task relation
 * @method TaskStateQuery rightJoinTask($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Task relation
 * @method TaskStateQuery innerJoinTask($relationAlias = null) Adds a INNER JOIN clause to the query using the Task relation
 *
 * @method TaskState findOne(PropelPDO $con = null) Return the first TaskState matching the query
 * @method TaskState findOneOrCreate(PropelPDO $con = null) Return the first TaskState matching the query, or a new TaskState object populated from the query conditions when no match is found
 *
 * @method TaskState findOneByTaskId(int $task_id) Return the first TaskState filtered by the task_id column
 * @method TaskState findOneByCode(string $code) Return the first TaskState filtered by the code column
 * @method TaskState findOneByNext(string $next) Return the first TaskState filtered by the next column
 * @method TaskState findOneByCurrent(boolean $current) Return the first TaskState filtered by the current column
 * @method TaskState findOneByCreatedAt(string $created_at) Return the first TaskState filtered by the created_at column
 * @method TaskState findOneByUpdatedAt(string $updated_at) Return the first TaskState filtered by the updated_at column
 *
 * @method array findById(int $id) Return TaskState objects filtered by the id column
 * @method array findByTaskId(int $task_id) Return TaskState objects filtered by the task_id column
 * @method array findByCode(string $code) Return TaskState objects filtered by the code column
 * @method array findByNext(string $next) Return TaskState objects filtered by the next column
 * @method array findByCurrent(boolean $current) Return TaskState objects filtered by the current column
 * @method array findByCreatedAt(string $created_at) Return TaskState objects filtered by the created_at column
 * @method array findByUpdatedAt(string $updated_at) Return TaskState objects filtered by the updated_at column
 */
abstract class BaseTaskStateQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseTaskStateQuery object.
     *
     * @param string $dbName     The dabase name
     * @param string $modelName  The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = 'EasyTask\\Bundle\\WorkflowBundle\\Model\\TaskState', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new TaskStateQuery object.
     *
     * @param string                  $modelAlias The alias of a model in the query
     * @param TaskStateQuery|Criteria $criteria   Optional Criteria to build the query from
     *
     * @return TaskStateQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof TaskStateQuery) {
            return $criteria;
        }
        $query = new TaskStateQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed     $key Primary key to use for the query
     * @param PropelPDO $con an optional connection object
     *
     * @return TaskState|TaskState[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = TaskStatePeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(TaskStatePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Alias of findPk to use instance pooling
     *
     * @param mixed     $key Primary key to use for the query
     * @param PropelPDO $con A connection object
     *
     * @return TaskState       A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneById($key, $con = null)
     {
        return $this->findPk($key, $con);
     }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param mixed     $key Primary key to use for the query
     * @param PropelPDO $con A connection object
     *
     * @return TaskState       A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id`, `task_id`, `code`, `next`, `current`, `created_at`, `updated_at` FROM `task_state` WHERE `id` = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new TaskState();
            $obj->hydrate($row);
            TaskStatePeer::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param mixed     $key Primary key to use for the query
     * @param PropelPDO $con A connection object
     *
     * @return TaskState|TaskState[]|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($stmt);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param array     $keys Primary keys to use for the query
     * @param PropelPDO $con  an optional connection object
     *
     * @return PropelObjectCollection|TaskState[]|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection($this->getDbName(), Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($stmt);
    }

    /**
     * Filter the query by primary key
     *
     * @param mixed $key Primary key to use for the query
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        return $this->addUsingAlias(TaskStatePeer::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param array $keys The list of primary key to use for the query
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        return $this->addUsingAlias(TaskStatePeer::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id >= 12
     * $query->filterById(array('max' => 12)); // WHERE id <= 12
     * </code>
     *
     * @param mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(TaskStatePeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(TaskStatePeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TaskStatePeer::ID, $id, $comparison);
    }

    /**
     * Filter the query on the task_id column
     *
     * Example usage:
     * <code>
     * $query->filterByTaskId(1234); // WHERE task_id = 1234
     * $query->filterByTaskId(array(12, 34)); // WHERE task_id IN (12, 34)
     * $query->filterByTaskId(array('min' => 12)); // WHERE task_id >= 12
     * $query->filterByTaskId(array('max' => 12)); // WHERE task_id <= 12
     * </code>
     *
     * @see       filterByTask()
     *
     * @param mixed $taskId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function filterByTaskId($taskId = null, $comparison = null)
    {
        if (is_array($taskId)) {
            $useMinMax = false;
            if (isset($taskId['min'])) {
                $this->addUsingAlias(TaskStatePeer::TASK_ID, $taskId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($taskId['max'])) {
                $this->addUsingAlias(TaskStatePeer::TASK_ID, $taskId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TaskStatePeer::TASK_ID, $taskId, $comparison);
    }

    /**
     * Filter the query on the code column
     *
     * Example usage:
     * <code>
     * $query->filterByCode('fooValue');   // WHERE code = 'fooValue'
     * $query->filterByCode('%fooValue%'); // WHERE code LIKE '%fooValue%'
     * </code>
     *
     * @param string $code The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function filterByCode($code = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($code)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $code)) {
                $code = str_replace('*', '%', $code);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(TaskStatePeer::CODE, $code, $comparison);
    }

    /**
     * Filter the query on the next column
     *
     * Example usage:
     * <code>
     * $query->filterByNext('fooValue');   // WHERE next = 'fooValue'
     * $query->filterByNext('%fooValue%'); // WHERE next LIKE '%fooValue%'
     * </code>
     *
     * @param string $next The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function filterByNext($next = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($next)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $next)) {
                $next = str_replace('*', '%', $next);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(TaskStatePeer::NEXT, $next, $comparison);
    }

    /**
     * Filter the query on the current column
     *
     * Example usage:
     * <code>
     * $query->filterByCurrent(true); // WHERE current = true
     * $query->filterByCurrent('yes'); // WHERE current = true
     * </code>
     *
     * @param boolean|string $current The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function filterByCurrent($current = null, $comparison = null)
    {
        if (is_string($current)) {
            $current = in_array(strtolower($current), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(TaskStatePeer::CURRENT, $current, $comparison);
    }

    /**
     * Filter the query on the created_at column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedAt('2011-03-14'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt('now'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt(array('max' => 'yesterday')); // WHERE created_at > '2011-03-13'
     * </code>
     *
     * @param mixed $createdAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(TaskStatePeer::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(TaskStatePeer::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TaskStatePeer::CREATED_AT, $createdAt, $comparison);
    }

    /**
     * Filter the query on the updated_at column
     *
     * Example usage:
     * <code>
     * $query->filterByUpdatedAt('2011-03-14'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt('now'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt(array('max' => 'yesterday')); // WHERE updated_at > '2011-03-13'
     * </code>
     *
     * @param mixed $updatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(TaskStatePeer::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(TaskStatePeer::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TaskStatePeer::UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query by a related Task object
     *
     * @param Task|PropelObjectCollection $task       The related object(s) to use as filter
     * @param string                      $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TaskStateQuery  The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTask($task, $comparison = null)
    {
        if ($task instanceof Task) {
            return $this
                ->addUsingAlias(TaskStatePeer::TASK_ID, $task->getId(), $comparison);
        } elseif ($task instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(TaskStatePeer::TASK_ID, $task->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByTask() only accepts arguments of type Task or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Task relation
     *
     * @param string $relationAlias optional alias for the relation
     * @param string $joinType      Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function joinTask($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Task');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Task');
        }

        return $this;
    }

    /**
     * Use the Task relation Task object
     *
     * @see       useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \EasyTask\Bundle\WorkflowBundle\Model\TaskQuery A secondary query class using the current class as primary query
     */
    public function useTaskQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinTask($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Task', '\EasyTask\Bundle\WorkflowBundle\Model\TaskQuery');
    }

    /**
     * Exclude object from result
     *
     * @param TaskState $taskState Object to remove from the list of results
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function prune($taskState = null)
    {
        if ($taskState) {
            $this->addUsingAlias(TaskStatePeer::ID, $taskState->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    // timestampable behavior

    /**
     * Filter by the latest updated
     *
     * @param int $nbDays Maximum age of the latest update in days
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(TaskStatePeer::UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(TaskStatePeer::UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(TaskStatePeer::UPDATED_AT);
    }

    /**
     * Filter by the latest created
     *
     * @param int $nbDays Maximum age of in days
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(TaskStatePeer::CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(TaskStatePeer::CREATED_AT);
    }

    /**
     * Order by create date asc
     *
     * @return TaskStateQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(TaskStatePeer::CREATED_AT);
    }
}
