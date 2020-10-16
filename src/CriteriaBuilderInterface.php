<?php declare(strict_types = 1);
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
use Everon\Component\Utils\Collection\ArrayableInterface;
use Everon\Component\Utils\Text\StringableInterface;

interface CriteriaBuilderInterface extends ArrayableInterface, StringableInterface, CriteriaBuilderFactoryWorkerAwareInterface
{
    /**
     * Starts new sub set of conditions
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @param string $glue
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     */
    public function where(
        string $column,
        string $operator,
        $value,
        string $glue = CriteriaBuilder::GLUE_AND
    ): CriteriaBuilderInterface;

    /**
     * Appends another condition to current set, using AND operator
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function andWhere(string $column, string $operator, $value): CriteriaBuilderInterface;

    /**
     * Appends another condition to current set, using OR operator
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function orWhere(string $column, string $operator, $value): CriteriaBuilderInterface;

    /**
     * Starts new sub set of conditions using raw SQL string
     *
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     * @param string $glue
     *
     * @return $this|\Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     */
    public function whereRaw(
        string $sql,
        ?array $value = null,
        string $customType = 'raw',
        string $glue = CriteriaBuilder::GLUE_AND
    ): CriteriaBuilderInterface;

    /**
     * Appends another condition to current set, using AND operator with raw SQL string
     *
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     *
     * @return $this|\Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function andWhereRaw(string $sql, array $value = null, string $customType = 'raw'): CriteriaBuilderInterface;

    /**
     * Appends another condition to current set, using OR operator with raw SQL string
     *
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     *
     * @return $this|\Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function orWhereRaw(string $sql, array $value = null, string $customType = 'raw'): CriteriaBuilderInterface;

    public function getCurrentContainer(): ContainerInterface;

    public function setCurrentContainer(ContainerInterface $Container);

    /**
     * @return \Everon\Component\CriteriaBuilder\CriteriaInterface[]|\Everon\Component\Collection\CollectionInterface
     */
    public function getContainerCollection(): CollectionInterface;

    /**
     * @param \Everon\Component\CriteriaBuilder\CriteriaInterface[]|\Everon\Component\Collection\CollectionInterface $ContainerCollection
     *
     * @return void
     */
    public function setContainerCollection(CollectionInterface $ContainerCollection): void;

    /**
     * Get operator joining current condition set
     *
     * @return string
     */
    public function getGlue(): string;

    /**
     * Reset operator joining current condition set
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     */
    public function resetGlue(): CriteriaBuilderInterface;

    /**
     * Join set of conditions with another set using AND operator
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     */
    public function glueByAnd(): CriteriaBuilderInterface;

    /**
     * Join set of conditions with another set using OR operator
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     */
    public function glueByOr(): CriteriaBuilderInterface;

    public function getGroupBy(): ?string;

    public function setGroupBy(?string $groupBy): CriteriaBuilderInterface;

    public function getLimit(): ?int;

    public function setLimit(?int $limit): CriteriaBuilderInterface;

    public function getOffset(): ?int;

    public function setOffset(?int $offset): CriteriaBuilderInterface;

    public function getOrderBy(): array;

    public function setOrderBy(array $orderBy): CriteriaBuilderInterface;

    /**
     * Get raw SQL template used to generate output, default is 'WHERE %s'
     *
     * @return string
     */
    public function getSqlTemplate(): string;

    /**
     * Set raw SQL template used to generate output, default is 'WHERE %s'
     *
     * @param string $sqlTemplate
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     */
    public function sql(string $sqlTemplate): CriteriaBuilderInterface;

    /**
     * @return \Everon\Component\CriteriaBuilder\SqlPartInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException
     */
    public function toSqlPart(): SqlPartInterface;

    public function appendContainerCollection(
        CollectionInterface $ContainerCollectionToMerge,
        ?string $glue = CriteriaBuilder::GLUE_AND
    ): void;

    /**
     * @param string $sqlOperator
     *
     * @return string
     * @throws \Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException
     */
    public static function getOperatorClassNameBySqlOperator(string $sqlOperator): string;

    /**
     * @param string $sqlType
     * @param string $operatorClassName
     *
     * @return void
     * @throws \Everon\Component\CriteriaBuilder\Exception\OperatorTypeAlreadyRegisteredException
     */
    public static function registerOperator(string $sqlType, string $operatorClassName): void;

    public static function randomizeParameterName(string $name): string;

    public function getOffsetLimitSql(): string;

    public function getOrderByAndSortSql(): string;

    public function getGroupBySql(): string;

    public static function getOperatorCollection(): CollectionInterface;

    public function resetParameterCollection(): void;

    public function getParameterCollection(): CollectionInterface;

    public function setParameterCollection(array $parameterCollection): CriteriaBuilderInterface;

    /**
     * Replaces . with _
     *
     * @param string $name
     * @param mixed $value
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     */
    public function setParameter(string $name, $value): CriteriaBuilderInterface;

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getParameter(string $name);
}
