<?php

namespace EasyTask\Bundle\LogBundle\Model\om;

use \Criteria;
use \Exception;
use \ModelCriteria;
use \PDO;
use \Propel;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use EasyTask\Bundle\LogBundle\Model\Log;
use EasyTask\Bundle\LogBundle\Model\LogPeer;
use EasyTask\Bundle\LogBundle\Model\LogQuery;

/**
 * @method LogQuery orderById($order = Criteria::ASC) Order by the id column
 * @method LogQuery orderByCode($order = Criteria::ASC) Order by the code column
 * @method LogQuery orderByMessage($order = Criteria::ASC) Order by the message column
 * @method LogQuery orderByTags($order = Criteria::ASC) Order by the tags column
 * @method LogQuery orderByDate($order = Criteria::ASC) Order by the date column
 *
 * @method LogQuery groupById() Group by the id column
 * @method LogQuery groupByCode() Group by the code column
 * @method LogQuery groupByMessage() Group by the message column
 * @method LogQuery groupByTags() Group by the tags column
 * @method LogQuery groupByDate() Group by the date column
 *
 * @method LogQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method LogQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method LogQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method Log findOne(PropelPDO $con = null) Return the first Log matching the query
 * @method Log findOneOrCreate(PropelPDO $con = null) Return the first Log matching the query, or a new Log object populated from the query conditions when no match is found
 *
 * @method Log findOneByCode(string $code) Return the first Log filtered by the code column
 * @method Log findOneByMessage(string $message) Return the first Log filtered by the message column
 * @method Log findOneByTags(string $tags) Return the first Log filtered by the tags column
 * @method Log findOneByDate(string $date) Return the first Log filtered by the date column
 *
 * @method array findById(int $id) Return Log objects filtered by the id column
 * @method array findByCode(string $code) Return Log objects filtered by the code column
 * @method array findByMessage(string $message) Return Log objects filtered by the message column
 * @method array findByTags(string $tags) Return Log objects filtered by the tags column
 * @method array findByDate(string $date) Return Log objects filtered by the date column
 */
abstract class BaseLogQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseLogQuery object.
     *
     * @param string $dbName     The dabase name
     * @param string $modelName  The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = 'EasyTask\\Bundle\\LogBundle\\Model\\Log', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new LogQuery object.
     *
     * @param string            $modelAlias The alias of a model in the query
     * @param LogQuery|Criteria $criteria   Optional Criteria to build the query from
     *
     * @return LogQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof LogQuery) {
            return $criteria;
        }
        $query = new LogQuery();
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
     * @return Log|Log[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = LogPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(LogPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return Log             A model object, or null if the key is not found
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
     * @return Log             A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id`, `code`, `message`, `tags`, `date` FROM `log` WHERE `id` = :p0';
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
            $obj = new Log();
            $obj->hydrate($row);
            LogPeer::addInstanceToPool($obj, (string) $key);
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
     * @return Log|Log[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|Log[]|mixed the list of results, formatted by the current formatter
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
     * @return LogQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        return $this->addUsingAlias(LogPeer::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param array $keys The list of primary key to use for the query
     *
     * @return LogQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        return $this->addUsingAlias(LogPeer::ID, $keys, Criteria::IN);
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
     * @return LogQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(LogPeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(LogPeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LogPeer::ID, $id, $comparison);
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
     * @return LogQuery The current query, for fluid interface
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

        return $this->addUsingAlias(LogPeer::CODE, $code, $comparison);
    }

    /**
     * Filter the query on the message column
     *
     * Example usage:
     * <code>
     * $query->filterByMessage('fooValue');   // WHERE message = 'fooValue'
     * $query->filterByMessage('%fooValue%'); // WHERE message LIKE '%fooValue%'
     * </code>
     *
     * @param string $message The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return LogQuery The current query, for fluid interface
     */
    public function filterByMessage($message = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($message)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $message)) {
                $message = str_replace('*', '%', $message);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(LogPeer::MESSAGE, $message, $comparison);
    }

    /**
     * Filter the query on the tags column
     *
     * Example usage:
     * <code>
     * $query->filterByTags('fooValue');   // WHERE tags = 'fooValue'
     * $query->filterByTags('%fooValue%'); // WHERE tags LIKE '%fooValue%'
     * </code>
     *
     * @param string $tags The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return LogQuery The current query, for fluid interface
     */
    public function filterByTags($tags = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($tags)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $tags)) {
                $tags = str_replace('*', '%', $tags);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(LogPeer::TAGS, $tags, $comparison);
    }

    /**
     * Filter the query on the date column
     *
     * Example usage:
     * <code>
     * $query->filterByDate('2011-03-14'); // WHERE date = '2011-03-14'
     * $query->filterByDate('now'); // WHERE date = '2011-03-14'
     * $query->filterByDate(array('max' => 'yesterday')); // WHERE date > '2011-03-13'
     * </code>
     *
     * @param mixed $date The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return LogQuery The current query, for fluid interface
     */
    public function filterByDate($date = null, $comparison = null)
    {
        if (is_array($date)) {
            $useMinMax = false;
            if (isset($date['min'])) {
                $this->addUsingAlias(LogPeer::DATE, $date['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($date['max'])) {
                $this->addUsingAlias(LogPeer::DATE, $date['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LogPeer::DATE, $date, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param Log $log Object to remove from the list of results
     *
     * @return LogQuery The current query, for fluid interface
     */
    public function prune($log = null)
    {
        if ($log) {
            $this->addUsingAlias(LogPeer::ID, $log->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}
