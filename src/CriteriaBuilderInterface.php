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
     * Appends to current sub set
     * 
     * @param string $column
     * @param string $operator
     * @param $value
     *
     * @return self
     */
    public function andWhere($column, $operator, $value);

    /**
     * Appends to current sub set
     * 
     * @param string $column
     * @param string $operator
     * @param $value
     *
     * @return self
     */
    public function orWhere($column, $operator, $value);

    /**
     * Starts new sub set of conditions
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
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     *
     * @return self
     */
    public function andWhereRaw($sql, array $value = null, $customType = 'raw');

    /**
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
     * @return CriteriaBuilderInterface
     */
    public function setGroupBy($group_by);

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @param int $limit
     *
     * @return CriteriaBuilderInterface
     */
    public function setLimit($limit);

    /**
     * @return int
     */
    public function getOffset();

    /**
     * @param int $offset
     *
     * @return CriteriaBuilderInterface
     */
    public function setOffset($offset);

    /**
     * @return array
     */
    public function getOrderBy();

    /**
     * @param array $order_by
     *
     * @return CriteriaBuilderInterface
     */
    public function setOrderBy(array $order_by);

    /**
     * @return string
     */
    public function getSqlTemplate();

    /**
     * @param string $sql_template
     *
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
    public function appendContainerCollection(CollectionInterface $ContainerCollectionToMerge, $glue=CriteriaBuilder::GLUE_AND);

    /**
     * @param string $sql_operator
     *
     * @throws UnknownOperatorTypeException
     *
     * @return string
     */
    public static function getOperatorClassNameBySqlOperator($sql_operator);

    /**
     * @param string $sql_type
     * @param $operator_class_name
     *
     * @return void
     */
    public static function registerOperator($sql_type, $operator_class_name);

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
    public function resetExtraParameterCollection();

    /**
     * @return CollectionInterface
     */
    public function getExtraParameterCollection();

    /**
     * @param array $extra_parameter_collection
     *
     * @return self
     */
    public function setExtraParameterCollection(array $extra_parameter_collection);

    /**
     * Replaces . with _
     *
     * @param string $name
     * @param $value
     *
     * @return self
     */
    public function setExtraParameter($name, $value);

    /**
     * @param string $name
     * @param $value
     *
     * @return
     */
    public function getExtraParameter($name);

}
