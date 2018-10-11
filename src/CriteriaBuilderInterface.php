<?php
/**
 * This file is part of the Everon components.
 *
 * (c) Oliwier Ptak <everonphp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder;

use Everon\Component\Collection\CollectionInterface;
use Everon\Component\CriteriaBuilder\Criteria\ContainerInterface;
use Everon\Component\CriteriaBuilder\Dependency\CriteriaBuilderFactoryWorkerAwareInterface;
use Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException;
use Everon\Component\Utils\Collection\ArrayableInterface;
use Everon\Component\Utils\Text\StringableInterface;

interface CriteriaBuilderInterface extends ArrayableInterface, StringableInterface, CriteriaBuilderFactoryWorkerAwareInterface
{

    /**
     * Starts new sub set of conditions
     *
     * @param string $column
     * @param string $operator
     * @param $value
     * @param string $glue
     *
     * @return self
     */
    public function where($column, $operator, $value, $glue = CriteriaBuilder::GLUE_AND);

    /**
     * Appends another condition to current set, using AND operator
     *
     * @param string $column
     * @param string $operator
     * @param $value
     *
     * @return self
     */
    public function andWhere($column, $operator, $value);

    /**
     * Appends another condition to current set, using OR operator
     *
     * @param string $column
     * @param string $operator
     * @param $value
     *
     * @return self
     */
    public function orWhere($column, $operator, $value);

    /**
     * Starts new sub set of conditions using raw SQL string
     *
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     * @param string $glue
     *
     * @return self
     */
    public function whereRaw($sql, array $value = null, $customType = 'raw', $glue = CriteriaBuilder::GLUE_AND);

    /**
     * Appends another condition to current set, using AND operator with raw SQL string
     *
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     *
     * @return self
     */
    public function andWhereRaw($sql, array $value = null, $customType = 'raw');

    /**
     * Appends another condition to current set, using OR operator with raw SQL string
     *
     * @param string $sql
     * @param array $value
     * @param string $customType
     *
     * @return self
     */
    public function orWhereRaw($sql, array $value = null, $customType = 'raw');

    /**
     * @return ContainerInterface
     */
    public function getCurrentContainer();

    /**
     * @param ContainerInterface $Container
     */
    public function setCurrentContainer(ContainerInterface $Container);

    /**
     * @return CollectionInterface|CriteriaInterface[]
     */
    public function getContainerCollection();

    /**
     * @param CollectionInterface $ContainerCollection
     */
    public function setContainerCollection(CollectionInterface $ContainerCollection);

    /**
     * Get operator joining current condition set
     *
     * @return string
     */
    public function getGlue();

    /**
     * Reset operator joining current condition set
     *
     * @return self
     */
    public function resetGlue();

    /**
     * Join set of conditions with another set using AND operator
     *
     * @return self
     */
    public function glueByAnd();

    /**
     * Join set of conditions with another set using OR operator
     *
     * @return self
     */
    public function glueByOr();

    /**
     * @return string
     */
    public function getGroupBy();

    /**
     * @param string $groupBy
     *
     * @return CriteriaBuilderInterface
     */
    public function setGroupBy($groupBy);

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @param int|null $limit
     *
     * @return CriteriaBuilderInterface
     */
    public function setLimit($limit);

    /**
     * @return int
     */
    public function getOffset();

    /**
     * @param int|null $offset
     *
     * @return CriteriaBuilderInterface
     */
    public function setOffset($offset);

    /**
     * @return array
     */
    public function getOrderBy();

    /**
     * @param array $orderBy
     *
     * @return CriteriaBuilderInterface
     */
    public function setOrderBy(array $orderBy);

    /**
     * Get raw SQL template used to generate output, default is 'WHERE %s'
     *
     * @return string
     */
    public function getSqlTemplate();

    /**
     * Set raw SQL template used to generate output, default is 'WHERE %s'
     *
     * @param string $sqlTemplate
     *
     * @return self
     */
    public function sql($sqlTemplate);

    /**
     * @return SqlPartInterface
     */
    public function toSqlPart();

    /**
     * @param CollectionInterface $ContainerCollectionToMerge
     * @param string $glue
     */
    public function appendContainerCollection(CollectionInterface $ContainerCollectionToMerge, $glue=CriteriaBuilder::GLUE_AND);

    /**
     * @param string $sqlOperator
     *
     * @throws UnknownOperatorTypeException
     *
     * @return string
     */
    public static function getOperatorClassNameBySqlOperator($sqlOperator);

    /**
     * @param string $sqlType
     * @param string $operatorClassName
     *
     * @return void
     */
    public static function registerOperator($sqlType, $operatorClassName);

    /**
     * @param string $name
     *
     * @return string
     */
    public static function randomizeParameterName($name);

    /**
     * @return string
     */
    public function getOffsetLimitSql();

    /**
     * @return string
     */
    public function getOrderByAndSortSql();

    /**
     * @return string
     */
    public function getGroupBySql();

    /**
     * @return CollectionInterface
     */
    public static function getOperatorCollection();

    /**
     * @return void
     */
    public function resetParameterCollection();

    /**
     * @return CollectionInterface
     */
    public function getParameterCollection();

    /**
     * @param array $parameterCollection
     *
     * @return self
     */
    public function setParameterCollection(array $parameterCollection);

    /**
     * Replaces . with _
     *
     * @param string $name
     * @param $value
     *
     * @return self
     */
    public function setParameter($name, $value);

    /**
     * @param string $name
     * @param $value
     *
     * @return mixed
     */
    public function getParameter($name);

}
