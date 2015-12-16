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
    public function where(string $column, string $operator, $value, $glue = CriteriaBuilder::GLUE_AND): CriteriaBuilderInterface;

    /**
     * Appends to current sub set
     * 
     * @param string $column
     * @param string $operator
     * @param $value
     *
     * @return self
     */
    public function andWhere(string $column, string $operator, $value): CriteriaBuilderInterface;

    /**
     * Appends to current sub set
     * 
     * @param string $column
     * @param string $operator
     * @param $value
     *
     * @return self
     */
    public function orWhere(string $column, string $operator, $value): CriteriaBuilderInterface;

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
    public function whereRaw(string $sql, array $value = null, string $customType = 'raw', $glue = CriteriaBuilder::GLUE_AND): CriteriaBuilderInterface;

    /**
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     *
     * @return self
     */
    public function andWhereRaw(string $sql, array $value = null, string $customType = 'raw'): CriteriaBuilderInterface;

    /**
     * @param string $sql
     * @param array $value
     * @param string $customType
     *
     * @return self
     */
    public function orWhereRaw(string $sql, array $value = null, string $customType = 'raw'): CriteriaBuilderInterface;

    /**
     * @return ContainerInterface
     */
    public function getCurrentContainer(): ContainerInterface;

    /**
     * @param ContainerInterface $Container
     */
    public function setCurrentContainer(ContainerInterface $Container);

    /**
     * @return CollectionInterface|CriteriaInterface[]
     */
    public function getContainerCollection(): CollectionInterface;

    /**
     * @param CollectionInterface $ContainerCollection
     */
    public function setContainerCollection(CollectionInterface $ContainerCollection);

    /**
     * @return string|null
     */
    public function getGlue();

    /**
     * @return CriteriaBuilderInterface
     */
    public function resetGlue(): CriteriaBuilderInterface;

    /**
     * @return CriteriaBuilderInterface
     */
    public function glueByAnd(): CriteriaBuilderInterface;

    /**
     * @return CriteriaBuilderInterface
     */
    public function glueByOr(): CriteriaBuilderInterface;

    /**
     * @return string|null
     */
    public function getGroupBy();

    /**
     * @param string|null $groupBy
     *
     * @return CriteriaBuilderInterface
     */
    public function setGroupBy($groupBy): CriteriaBuilderInterface;

    /**
     * @return int|null
     */
    public function getLimit();

    /**
     * @param int|null $limit
     *
     * @return CriteriaBuilderInterface
     */
    public function setLimit($limit): CriteriaBuilderInterface;

    /**
     * @return int|null
     */
    public function getOffset();

    /**
     * @param int|null $offset
     *
     * @return CriteriaBuilderInterface
     */
    public function setOffset($offset): CriteriaBuilderInterface;

    /**
     * @return array
     */
    public function getOrderBy();

    /**
     * @param array $orderBy
     *
     * @return CriteriaBuilderInterface
     */
    public function setOrderBy(array $orderBy): CriteriaBuilderInterface;

    /**
     * @return string
     */
    public function getSqlTemplate();

    /**
     * @param string $sqlTemplate
     *
     * @return CriteriaBuilderInterface
     */
    public function sql($sqlTemplate): CriteriaBuilderInterface;

    /**
     * @return SqlPartInterface
     */
    public function toSqlPart(): SqlPartInterface;

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
    public static function getOperatorClassNameBySqlOperator($sqlOperator): string;

    /**
     * @param string $sqlType
     * @param $operatorClassName
     *
     * @return void
     */
    public static function registerOperator($sqlType, $operatorClassName);

    /**
     * @param string $name
     *
     * @return string
     */
    public static function randomizeParameterName($name): string;

    /**
     * @return string
     */
    public function getOffsetLimitSql(): string;

    /**
     * @return string
     */
    public function getOrderByAndSortSql(): string;

    /**
     * @return string
     */
    public function getGroupBySql(): string;

    /**
     * @return CollectionInterface
     */
    public static function getOperatorCollection(): CollectionInterface;

    /**
     * @return void
     */
    public function resetParameterCollection();

    /**
     * @return CollectionInterface
     */
    public function getParameterCollection(): CollectionInterface;

    /**
     * @param array $parameterCollection
     *
     * @return CriteriaBuilderInterface
     */
    public function setParameterCollection(array $parameterCollection): CriteriaBuilderInterface;

    /**
     * Replaces . with _
     *
     * @param string $name
     * @param $value
     *
     * @return CriteriaBuilderInterface
     */
    public function setParameter($name, $value): CriteriaBuilderInterface;

    /**
     * @param string|int $name
     * @param $value
     *
     * @return
     */
    public function getParameter($name);

}
