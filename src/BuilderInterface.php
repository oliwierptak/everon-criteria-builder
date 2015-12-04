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
use Everon\Component\CriteriaBuilder\Dependency\CriteriaBuilderFactoryWorkerAwareInterface;
use Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException;
use Everon\Component\Utils\Collection\ArrayableInterface;
use Everon\Component\Utils\Text\StringableInterface;

interface BuilderInterface extends ArrayableInterface, StringableInterface, CriteriaBuilderFactoryWorkerAwareInterface
{

    /**
     * Starts new Sub Query
     * 
     * @param $column
     * @param $operator
     * @param $value
     * @param $glue
     *
     * @return self
     */
    public function where($column, $operator, $value, $glue = Builder::GLUE_AND);

    /**
     * Appends to current subquery
     * 
     * @param $column
     * @param $operator
     * @param $value
     *
     * @return Builder
     */
    public function andWhere($column, $operator, $value);

    /**
     * Appends to current subquery
     * 
     * @param $column
     * @param $operator
     * @param $value
     *
     * @return Builder
     */
    public function orWhere($column, $operator, $value);

    /**
     * @param $sql
     * @param array|null $value
     * @param string $customType
     * @param string $glue
     *
     * @return self
     */
    public function whereRaw($sql, array $value = null, $customType = 'raw', $glue = Builder::GLUE_AND);

    /**
     * @param $sql
     * @param array|null $value
     * @param string $customType
     *
     * @return self
     */
    public function andWhereRaw($sql, array $value = null, $customType = 'raw');

    /**
     * @param $sql
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
     * @return string
     */
    public function getGlue();

    /**
     * @return self
     */
    public function resetGlue();

    /**
     * @return self
     */
    public function glueByAnd();

    /**
     * @return self
     */
    public function glueByOr();

    /**
     * @return string
     */
    public function getGroupBy();

    /**
     * @param string $group_by
     *
     * @return BuilderInterface
     */
    public function setGroupBy($group_by);

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @param int $limit
     *
     * @return BuilderInterface
     */
    public function setLimit($limit);

    /**
     * @return int
     */
    public function getOffset();

    /**
     * @param int $offset
     *
     * @return BuilderInterface
     */
    public function setOffset($offset);

    /**
     * @return array
     */
    public function getOrderBy();

    /**
     * @param array $order_by
     *
     * @return BuilderInterface
     */
    public function setOrderBy(array $order_by);

    /**
     * @return string
     */
    public function getSqlTemplate();

    /**
     * @param string $sql_template
     * @return self
     */
    public function sql($sql_template);

    /**
     * @return SqlPartInterface
     */
    public function toSqlPart();

    /**
     * @param CollectionInterface $ContainerCollectionToMerge
     * @param string $glue
     */
    public function appendContainerCollection(CollectionInterface $ContainerCollectionToMerge, $glue=Builder::GLUE_AND);

    /**
     * @param $sql_operator
     *
     * @throws UnknownOperatorTypeException
     *
     * @return string
     */
    public static function getOperatorClassNameBySqlOperator($sql_operator);

    /**
     * @param $sql_type
     * @param $operator_class_name
     *
     * @return void
     */
    public static function registerOperator($sql_type, $operator_class_name);

    /**
     * @param $name
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

}
