<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <EveronFramework@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder;

use Everon\Component\Collection\CollectionInterface;
use Everon\Component\CriteriaBuilder\Criteria\ContainerInterface;
use Everon\Component\CriteriaBuilder\Dependency\CriteriaBuilderFactoryWorkerDependencyInterface;
use Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException;
use Everon\Component\Utils\Collection\ArrayableInterface;
use Everon\Component\Utils\Text\StringableInterface;

interface BuilderInterface extends ArrayableInterface, StringableInterface, CriteriaBuilderFactoryWorkerDependencyInterface
{
    /**
     * Starts new Sub Query
     * 
     * @param $column
     * @param $operator
     * @param $value
     * @param $glue
     * @return $this
     */
    function where($column, $operator, $value, $glue = Builder::GLUE_AND);
        
    /**
     * Appends to current subquery
     * 
     * @param $column
     * @param $operator
     * @param $value
     * @return Builder
     */
    function andWhere($column, $operator, $value);

    /**
     * Appends to current subquery
     * 
     * @param $column
     * @param $operator
     * @param $value
     * @return Builder
     */
    function orWhere($column, $operator, $value);

    /**
     * @param $sql
     * @param array|null $value
     * @param string $glue
     * @return $this
     */
    function whereRaw($sql, array $value = null, $glue = Builder::GLUE_AND);

    /**
     * @param $sql
     * @param array|null $value
     * @return $this
     */
    function andWhereRaw($sql, array $value = null);

    /**
     * @param $sql
     * @param array $value
     * @return $this
     */
    function orWhereRaw($sql, array $value = null);

    /**
     * @return ContainerInterface
     */
    function getCurrentContainer();

    /**
     * @param ContainerInterface $Container
     */
    function setCurrentContainer(ContainerInterface $Container);

    /**
     * @return CollectionInterface
     */
    function getContainerCollection();

    /**
     * @param CollectionInterface $ContainerCollection
     */
    function setContainerCollection(CollectionInterface $ContainerCollection);

    /**
     * @return string
     */
    function getGlue();

    function resetGlue();

    function glueByAnd();

    function glueByOr();

    /**
     * @return string
     */
    function getGroupBy();

    /**
     * @param string $group_by
     * @return BuilderInterface
     */
    function setGroupBy($group_by);

    /**
     * @return int
     */
    function getLimit();

    /**
     * @param int $limit
     * @return BuilderInterface
     */
    function setLimit($limit);

    /**
     * @return int
     */
    function getOffset();

    /**
     * @param int $offset
     * @return BuilderInterface
     */
    function setOffset($offset);
    
    /**
     * @return array
     */
    function getOrderBy();

    /**
     * @param array $order_by
     * @return BuilderInterface
     */
    function setOrderBy(array $order_by);
    
    /**
     * @return SqlPartInterface
     */
    function toSqlPart();

    /**
     * @param CollectionInterface $ContainerCollectionToMerge
     * @param string $glue
     */
    function appendContainerCollection(CollectionInterface $ContainerCollectionToMerge, $glue=Builder::GLUE_AND);

    /**
     * @param $operator
     * @return string
     * @throws UnknownOperatorTypeException
     */
    static function getOperatorClassNameBySqlOperator($operator);

    /**
     * @param $name
     * @return string
     */
    static function randomizeParameterName($name);

    /**
     * @return string
     */
    function getOffsetLimitSql();

    /**
     * @return string
     */
    function getOrderByAndSortSql();

    /**
     * @return string
     */
    function getGroupBySql();
}
