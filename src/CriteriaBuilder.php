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

use Everon\Component\Collection\Collection;
use Everon\Component\Collection\CollectionInterface;
use Everon\Component\CriteriaBuilder\Criteria\ContainerInterface;
use Everon\Component\CriteriaBuilder\Exception\OperatorTypeAlreadyRegisteredException;
use Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException;
use Everon\Component\Utils\Collection\MergeDefault;
use Everon\Component\Utils\Collection\ToArray;
use Everon\Component\Utils\Text\ToString;

class CriteriaBuilder implements CriteriaBuilderInterface
{
    use Dependency\Setter\CriteriaBuilderFactoryWorker;

    use MergeDefault;
    use ToArray;
    use ToString;

    const GLUE_AND = 'AND';
    const GLUE_OR = 'OR';

    /**
     * @var \Everon\Component\Collection\CollectionInterface|\Everon\Component\CriteriaBuilder\OperatorInterface[]
     */
    protected static $OperatorCollection;

    /**
     * @var int
     */
    protected $currentContainerIndex = -1;

    /**
     * @var \Everon\Component\CriteriaBuilder\CriteriaInterface[]|\Everon\Component\Collection\CollectionInterface
     */
    protected $ContainerCollection;

    /**
     * @var string
     */
    protected $glue = self::GLUE_AND;

    /**
     * @var int|null
     */
    protected $offset = null;

    /**
     * @var int|null
     */
    protected $limit = null;

    /**
     * @var array
     */
    protected $orderBy = [];

    /**
     * @var string|null
     */
    protected $groupBy = null;

    /**
     * @var string
     */
    protected $sqlTemplate = 'WHERE %s';

    /**
     * @var string
     */
    protected $defaultSqlTemplate = 'WHERE %s';

    /**
     * @var bool
     */
    protected $isSequenceOpened = false;

    /**
     * @var \Everon\Component\Collection\CollectionInterface
     */
    protected $ParameterCollection;

    protected function getArrayableData(): array
    {
        $SqlPart = $this->toSqlPart();

        return $SqlPart->getParameters();
    }

    protected function getToString(): string
    {
        $SqlPart = $this->toSqlPart();

        return $SqlPart->getSql();
    }

    /**
     * @param \Everon\Component\CriteriaBuilder\Criteria\ContainerInterface $Container
     *
     * @return string
     * @throws \Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException
     */
    protected function criteriaToSql(ContainerInterface $Container): string
    {
        /*
         * @var CriteriumInterface $Criterium
         */
        $sql = '';
        foreach ($Container->getCriteria()->getCriteriumCollection() as $Criterium) {
            $SqlPart = $Criterium->getSqlPart();
            $sql .= ltrim($Criterium->getGlue() . ' ' . $SqlPart->getSql() . ' ');
        }

        return '(' . rtrim($sql) . ')';
    }

    /**
     * @param \Everon\Component\CriteriaBuilder\Criteria\ContainerInterface $Container
     *
     * @return array
     * @throws \Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException
     */
    protected function criteriaToParameters(ContainerInterface $Container): array
    {
        /*
         * @var CriteriumInterface $Criterium
         */
        $parameters = [];
        foreach ($Container->getCriteria()->getCriteriumCollection() as $Criterium) {
            $SqlPart = $Criterium->getSqlPart();
            $parameters[] = $SqlPart->getParameters();
        }

        return $parameters;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @param string $glue
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     */
    public function where(
        string $column,
        string $operator,
        $value,
        string $glue = CriteriaBuilder::GLUE_AND
    ): CriteriaBuilderInterface {
        $this->openSequence();

        if ($this->currentContainerIndex === 0) {
            $this->getCurrentContainer()->resetGlue(); //reset glue for first item
        }

        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($column, $operator, $value);
        $this->getCurrentContainer()->getCriteria()->where($Criterium);

        $this->closeSequence();

        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed $value
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function andWhere(string $column, string $operator, $value): CriteriaBuilderInterface
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($column, $operator, $value);
        if ($this->currentContainerIndex < 0) {
            $this->where($column, $operator, $value);
        }
        else {
            $this->getCurrentContainer()->getCriteria()->andWhere($Criterium);
        }

        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed $value
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function orWhere(string $column, string $operator, $value): CriteriaBuilderInterface
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($column, $operator, $value);
        if ($this->currentContainerIndex < 0) {
            $this->where($column, $operator, $value);
        }
        else {
            $this->getCurrentContainer()->getCriteria()->orWhere($Criterium);
        }

        return $this;
    }

    /**
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     * @param string $glue
     *
     * @return $this|\Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     */
    public function whereRaw(
        string $sql,
        array $value = null,
        string $customType = 'raw',
        string $glue = CriteriaBuilder::GLUE_AND
    ): CriteriaBuilderInterface {
        $this->openSequence();

        if ($this->currentContainerIndex === 0) {
            $this->getCurrentContainer()->resetGlue(); //reset glue for first item
        }

        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($sql, $customType, $value);
        $this->getCurrentContainer()->getCriteria()->where($Criterium);

        $this->closeSequence();

        return $this;
    }

    /**
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     *
     * @return $this|\Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function andWhereRaw(string $sql, array $value = null, string $customType = 'raw'): CriteriaBuilderInterface
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($sql, $customType, $value);
        if ($this->currentContainerIndex < 0) {
            $this->whereRaw($sql, $value, $customType);
        }
        else {
            $this->getCurrentContainer()->getCriteria()->andWhere($Criterium);
        }

        return $this;
    }

    /**
     * @param string $sql
     * @param array|null $value
     * @param string $customType
     *
     * @return $this|\Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function orWhereRaw(string $sql, array $value = null, string $customType = 'raw'): CriteriaBuilderInterface
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($sql, $customType, $value);
        if ($this->currentContainerIndex < 0) {
            $this->whereRaw($sql, $value, $customType);
        }
        else {
            $this->getCurrentContainer()->getCriteria()->orWhere($Criterium);
        }

        return $this;
    }

    public function getCurrentContainer(): ContainerInterface
    {
        if ($this->getContainerCollection()->has($this->currentContainerIndex) === false) {
            $Criteria = $this->getFactoryWorker()->buildCriteria();
            $Container = $this->getFactoryWorker()->buildCriteriaContainer($Criteria, null);
            $this->getContainerCollection()->set($this->currentContainerIndex, $Container);
        }

        return $this->getContainerCollection()->get($this->currentContainerIndex);
    }

    public function setCurrentContainer(ContainerInterface $Container)
    {
        $this->ContainerCollection[$this->currentContainerIndex] = $Container;
    }

    /**
     * @return \Everon\Component\CriteriaBuilder\CriteriaInterface[]|\Everon\Component\Collection\CollectionInterface
     */
    public function getContainerCollection(): CollectionInterface
    {
        if ($this->ContainerCollection === null) {
            $this->ContainerCollection = new Collection([]);
        }

        return $this->ContainerCollection;
    }

    public function setContainerCollection(CollectionInterface $ContainerCollection): void
    {
        $this->ContainerCollection = $ContainerCollection;
    }

    /**
     * @return string
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     */
    public function getGlue(): string
    {
        return $this->getCurrentContainer()->getGlue();
    }

    /**
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     */
    public function resetGlue(): CriteriaBuilderInterface
    {
        $this->openSequence();
        $this->getCurrentContainer()->resetGlue();

        return $this;
    }

    /**
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     */
    public function glueByAnd(): CriteriaBuilderInterface
    {
        $this->openSequence();
        $this->getCurrentContainer()->glueByAnd();

        return $this;
    }

    /**
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     */
    public function glueByOr(): CriteriaBuilderInterface
    {
        $this->openSequence();
        $this->getCurrentContainer()->glueByOr();

        return $this;
    }

    public function getGroupBy(): ?string
    {
        return $this->groupBy;
    }

    public function setGroupBy(?string $groupBy): CriteriaBuilderInterface
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): CriteriaBuilderInterface
    {
        $this->limit = $limit;

        return $this;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function setOffset(?int $offset): CriteriaBuilderInterface
    {
        $this->offset = $offset;

        return $this;
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function setOrderBy(array $orderBy): CriteriaBuilderInterface
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getSqlTemplate(): string
    {
        return $this->sqlTemplate;
    }

    public function sql(string $sqlTemplate): CriteriaBuilderInterface
    {
        $this->sqlTemplate = $sqlTemplate;

        return $this;
    }

    public function getOffsetLimitSql(): string
    {
        if ($this->getLimit() === null && $this->getOffset() === null) {
            return '';
        }

        if ($this->getLimit() === null && ($this->getOffset() !== null && (int) $this->getOffset() !== 0)) {
            return 'OFFSET ' . $this->offset;
        }

        if ((int) $this->getLimit() !== 0 && $this->getOffset() === null) {
            return 'LIMIT ' . $this->getLimit();
        }

        return 'LIMIT ' . $this->getLimit() . ' OFFSET ' . $this->getOffset();
    }

    public function getOrderByAndSortSql(): string
    {
        if (is_array($this->getOrderBy()) === false || empty($this->getOrderBy())) {
            return '';
        }

        $order_by = '';
        foreach ($this->getOrderBy() as $name => $sort) {
            $order_by .= "${name} " . $sort . ',';
        }

        if ($order_by !== '') {
            $order_by = trim($order_by, ',');
            $order_by = 'ORDER BY ' . $order_by;
        }

        return $order_by;
    }

    public function getGroupBySql(): string
    {
        if ($this->getGroupBy() === null) {
            return '';
        }

        return 'GROUP BY ' . $this->getGroupBy();
    }

    /**
     * @return \Everon\Component\CriteriaBuilder\SqlPartInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException
     */
    public function toSqlPart(): SqlPartInterface
    {
        $sqlTokens = [];
        $parameters = [];
        $glue = null;

        foreach ($this->getContainerCollection() as $Container) {
            if ($Container->getCriteria()->getCriteriumCollection()->isEmpty()) {
                continue;
            }

            $glue = $this->resetGlueOnFirstIteration($sqlTokens, $Container);
            $sqlTokens[] = $glue . $this->criteriaToSql($Container);

            $criteriaParameters = $this->criteriaToParameters($Container);
            $parameters = $this->mergeParametersDefaults($criteriaParameters, $parameters);
        }

        $parameters = $this->collectionMergeDefault($parameters, $this->getParameterCollection()->toArray());
        $sqlQuery = $this->formatSqlQuery($sqlTokens, $glue);

        return $this->getFactoryWorker()->buildSqlPart($sqlQuery, $parameters);
    }

    protected function formatSqlQuery(array $sqlTokens, ?string $glue): string
    {
        $sql_query = implode("\n", $sqlTokens);
        $sql_query = rtrim($sql_query, $glue . ' ');

        $sql_query = empty($sqlTokens) === false || $this->hasCustomSqlTemplate() ? sprintf(
            $this->getSqlTemplate(),
            $sql_query
        ) : $sql_query;
        
        $sql_query .= ' ' . trim(
                $this->getGroupBySql() . ' ' . $this->getOrderByAndSortSql() . ' ' . $this->getOffsetLimitSql()
            );

        return trim($sql_query);
    }

    protected function hasCustomSqlTemplate(): bool
    {
        return $this->getSqlTemplate() !== $this->defaultSqlTemplate;
    }

    public function appendContainerCollection(
        CollectionInterface $ContainerCollectionToMerge,
        ?string $glue = CriteriaBuilder::GLUE_AND
    ): void {
        foreach ($ContainerCollectionToMerge as $ContainerToMerge) {
            if ($ContainerToMerge->getGlue() === null) {
                $ContainerToMerge->setGlue($glue);
            }
            $this->getContainerCollection()->append($ContainerToMerge);
        }
    }

    /**
     * @param string $sqlOperator
     *
     * @return mixed|null
     * @throws \Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException
     */
    public static function getOperatorClassNameBySqlOperator(string $sqlOperator): string
    {
        $sqlOperator = strtoupper(trim($sqlOperator));
        if (static::getOperatorCollection()->has($sqlOperator) === false) {
            throw new UnknownOperatorTypeException($sqlOperator);
        }

        return static::getOperatorCollection()->get($sqlOperator);
    }

    /**
     * @param string $sqlType
     * @param string $operatorClassName
     *
     * @return void
     * @throws \Everon\Component\CriteriaBuilder\Exception\OperatorTypeAlreadyRegisteredException
     */
    public static function registerOperator(string $sqlType, string $operatorClassName): void
    {
        $sqlType = strtoupper(trim($sqlType));
        if (static::getOperatorCollection()->has($sqlType)) {
            throw new OperatorTypeAlreadyRegisteredException($sqlType);
        }

        static::getOperatorCollection()->set($sqlType, $operatorClassName);
    }

    public static function randomizeParameterName(string $name): string
    {
        return $name . '_' . mt_rand(100, time());
    }

    public static function getOperatorCollection(): CollectionInterface
    {
        if (static::$OperatorCollection === null) {
            static::$OperatorCollection = new Collection(
                [
                    Operator\Between::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\Between::class,
                    Operator\Equal::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\Equal::class,
                    Operator\GreaterOrEqual::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\GreaterOrEqual::class,
                    Operator\GreaterThen::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\GreaterThen::class,
                    Operator\Ilike::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\Ilike::class,
                    Operator\In::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\In::class,
                    Operator\Is::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\Is::class,
                    Operator\Like::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\Like::class,
                    Operator\NotBetween::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\NotBetween::class,
                    Operator\NotEqual::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\NotEqual::class,
                    Operator\NotIlike::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\NotIlike::class,
                    Operator\NotIn::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\NotIn::class,
                    Operator\NotIs::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\NotIs::class,
                    Operator\NotLike::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\NotLike::class,
                    Operator\Raw::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\Raw::class,
                    Operator\SmallerOrEqual::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\SmallerOrEqual::class,
                    Operator\SmallerThen::TYPE_AS_SQL => \Everon\Component\CriteriaBuilder\Operator\SmallerThen::class,
                ]
            );
        }

        return static::$OperatorCollection;
    }

    protected function getFactoryWorker(): CriteriaBuilderFactoryWorkerInterface
    {
        return $this->getCriteriaBuilderFactoryWorker();
    }

    protected function openSequence(): void
    {
        if ($this->isSequenceOpened) {
            return;
        }

        $this->currentContainerIndex++;
        $this->isSequenceOpened = true;
    }

    protected function closeSequence(): void
    {
        $this->isSequenceOpened = false;
    }

    protected function mergeParametersDefaults(array $criteriaParameters, array $parameters): array
    {
        $tmp = [];
        foreach ($criteriaParameters as $cpValues) {
            $tmp = $this->collectionMergeDefault($tmp, $cpValues);
        }

        $parameters = $this->collectionMergeDefault($tmp, $parameters);

        return $parameters;
    }

    protected function resetGlueOnFirstIteration(array $sqlTokens, ContainerInterface $Container): string
    {
        $glue = (count($sqlTokens) === 0) ? '' : $Container->getGlue() . ' ';

        return $glue;
    }

    public function resetParameterCollection(): void
    {
        $this->ParameterCollection = null;
    }

    public function getParameterCollection(): CollectionInterface
    {
        if ($this->ParameterCollection === null) {
            $this->ParameterCollection = new Collection([]);
        }

        return $this->ParameterCollection;
    }

    public function setParameterCollection(array $parameterCollection): CriteriaBuilderInterface
    {
        foreach ($parameterCollection as $key => $value) {
            $this->setParameter($key, $value);
        }

        return $this;
    }

    public function setParameter(string $name, $value): CriteriaBuilderInterface
    {
        $name = str_replace('.', '_', $name);
        $this->getParameterCollection()->set($name, $value);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getParameter(string $name)
    {
        return $this->getParameterCollection()->get($name);
    }
}
