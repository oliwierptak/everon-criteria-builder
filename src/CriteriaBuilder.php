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
     * @var CollectionInterface
     */
    protected static $OperatorCollection;

    /**
     * @var string
     */
    protected $currentContainerIndex = -1;

    /**
     * @var CollectionInterface
     */
    protected $ContainerCollection;

    /**
     * @var string
     */
    protected $glue = self::GLUE_AND;

    /**
     * @var int
     */
    protected $offset = null;

    /**
     * @var int
     */
    protected $limit = null;

    /**
     * @var array
     */
    protected $orderBy = [];

    /**
     * @var string
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
     * @var CollectionInterface
     */
    protected $ParameterCollection;

    /**
     * @return array
     */
    protected function getArrayableData(): array
    {
        $SqlPart = $this->toSqlPart();

        return $SqlPart->getParameters();
    }

    /**
     * @return string
     */
    protected function getToString(): string
    {
        $SqlPart = $this->toSqlPart();

        return $SqlPart->getSql();
    }

    /**
     * @param ContainerInterface $Container
     *
     * @return string
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
     * @param ContainerInterface $Container
     *
     * @return array
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
     * @inheritdoc
     */
    public function where(string $column, string $operator, $value, $glue = self::GLUE_AND): CriteriaBuilderInterface
    {
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
     * @inheritdoc
     */
    public function andWhere(string $column, string $operator, $value): CriteriaBuilderInterface
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($column, $operator, $value);
        if ($this->currentContainerIndex < 0) {
            $this->where($column, $operator, $value);
        } else {
            $this->getCurrentContainer()->getCriteria()->andWhere($Criterium);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function orWhere(string $column, string $operator, $value): CriteriaBuilderInterface
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($column, $operator, $value);
        if ($this->currentContainerIndex < 0) {
            $this->where($column, $operator, $value);
        } else {
            $this->getCurrentContainer()->getCriteria()->orWhere($Criterium);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function whereRaw(string $sql, array $value = null, string $customType = 'raw', $glue = self::GLUE_AND): CriteriaBuilderInterface
    {
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
     * @inheritdoc
     */
    public function andWhereRaw(string $sql, array $value = null, string $customType = 'raw'): CriteriaBuilderInterface
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($sql, $customType, $value);
        if ($this->currentContainerIndex < 0) {
            $this->whereRaw($sql, $customType, $value);
        } else {
            $this->getCurrentContainer()->getCriteria()->andWhere($Criterium);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function orWhereRaw(string $sql, array $value = null, string $customType = 'raw'): CriteriaBuilderInterface
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($sql, $customType, $value);
        if ($this->currentContainerIndex < 0) {
            $this->whereRaw($sql, $customType, $value);
        } else {
            $this->getCurrentContainer()->getCriteria()->orWhere($Criterium);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentContainer(): ContainerInterface
    {
        if ($this->getContainerCollection()->has($this->currentContainerIndex) === false) {
            $Criteria = $this->getFactoryWorker()->buildCriteria();
            $Container = $this->getFactoryWorker()->buildCriteriaContainer($Criteria, null);
            $this->getContainerCollection()->set($this->currentContainerIndex, $Container);
        }

        return $this->getContainerCollection()->get($this->currentContainerIndex);
    }

    /**
     * @inheritdoc
     */
    public function setCurrentContainer(ContainerInterface $Container)
    {
        $this->ContainerCollection[$this->currentContainerIndex] = $Container;
    }

    /**
     * @inheritdoc
     */
    public function getContainerCollection(): CollectionInterface
    {
        if ($this->ContainerCollection === null) {
            $this->ContainerCollection = new Collection([]);
        }

        return $this->ContainerCollection;
    }

    /**
     * @inheritdoc
     */
    public function setContainerCollection(CollectionInterface $ContainerCollection)
    {
        $this->ContainerCollection = $ContainerCollection;
    }

    /**
     * @inheritdoc
     */
    public function getGlue()
    {
        return $this->getCurrentContainer()->getGlue();
    }

    /**
     * @inheritdoc
     */
    public function resetGlue(): CriteriaBuilderInterface
    {
        $this->openSequence();
        $this->getCurrentContainer()->resetGlue();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function glueByAnd(): CriteriaBuilderInterface
    {
        $this->openSequence();
        $this->getCurrentContainer()->glueByAnd();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function glueByOr(): CriteriaBuilderInterface
    {
        $this->openSequence();
        $this->getCurrentContainer()->glueByOr();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @inheritdoc
     */
    public function setGroupBy($groupBy): CriteriaBuilderInterface
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @inheritdoc
     */
    public function setLimit($limit): CriteriaBuilderInterface
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @inheritdoc
     */
    public function setOffset($offset): CriteriaBuilderInterface
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @inheritdoc
     */
    public function setOrderBy(array $orderBy): CriteriaBuilderInterface
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSqlTemplate(): string
    {
        return $this->sqlTemplate;
    }

    /**
     * @inheritdoc
     */
    public function sql($sqlTemplate): CriteriaBuilderInterface
    {
        $this->sqlTemplate = $sqlTemplate;

        return $this;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getGroupBySql(): string
    {
        if ($this->getGroupBy() === null) {
            return '';
        }

        return 'GROUP BY ' . $this->getGroupBy();
    }

    /**
     * @inheritdoc
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

    /**
     * @param array $sqlTokens
     * @param string|null $glue
     *
     * @return string
     */
    protected function formatSqlQuery(array $sqlTokens, $glue): string
    {
        $sql_query = implode("\n", $sqlTokens);
        $sql_query = rtrim($sql_query, $glue . ' ');

        $sql_query = empty($sqlTokens) === false || $this->hasCustomSqlTemplate() ? sprintf($this->getSqlTemplate(), $sql_query) : $sql_query;
        $sql_query .= ' ' . trim($this->getGroupBySql() . ' ' . $this->getOrderByAndSortSql() . ' ' . $this->getOffsetLimitSql());

        return trim($sql_query);
    }

    /**
     * @return bool
     */
    protected function hasCustomSqlTemplate(): bool
    {
        return $this->getSqlTemplate() !== $this->defaultSqlTemplate;
    }

    /**
     * @inheritdoc
     */
    public function appendContainerCollection(CollectionInterface $ContainerCollectionToMerge, $glue = self::GLUE_AND)
    {
        foreach ($ContainerCollectionToMerge as $ContainerToMerge) {
            if ($ContainerToMerge->getGlue() === null) {
                $ContainerToMerge->setGlue($glue);
            }
            $this->getContainerCollection()->append($ContainerToMerge);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getOperatorClassNameBySqlOperator($sqlOperator): string
    {
        $sqlOperator = strtoupper(trim($sqlOperator));
        if (static::getOperatorCollection()->has($sqlOperator) === false) {
            throw new UnknownOperatorTypeException($sqlOperator);
        }

        return static::getOperatorCollection()->get($sqlOperator);
    }

    /**
     * @inheritdoc
     */
    public static function registerOperator($sqlType, $operatorClassName)
    {
        $sqlType = strtoupper(trim($sqlType));
        if (static::getOperatorCollection()->has($sqlType)) {
            throw new OperatorTypeAlreadyRegisteredException($sqlType);
        }

        static::getOperatorCollection()->set($sqlType, $operatorClassName);
    }

    /**
     * @inheritdoc
     */
    public static function randomizeParameterName($name): string
    {
        return $name . '_' . mt_rand(100, time());
    }

    /**
     * @inheritdoc
     */
    public static function getOperatorCollection(): CollectionInterface
    {
        if (static::$OperatorCollection === null) {
            static::$OperatorCollection = new Collection([
                Operator\Between::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\Between',
                Operator\Equal::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\Equal',
                Operator\GreaterOrEqual::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\GreaterOrEqual',
                Operator\GreaterThen::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\GreaterThen',
                Operator\Ilike::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\Ilike',
                Operator\In::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\In',
                Operator\Is::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\Is',
                Operator\Like::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\Like',
                Operator\NotBetween::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\NotBetween',
                Operator\NotEqual::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\NotEqual',
                Operator\NotIlike::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\NotIlike',
                Operator\NotIn::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\NotIn',
                Operator\NotIs::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\NotIs',
                Operator\NotLike::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\NotLike',
                Operator\Raw::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\Raw',
                Operator\SmallerOrEqual::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\SmallerOrEqual',
                Operator\SmallerThen::TYPE_AS_SQL => 'Everon\Component\CriteriaBuilder\Operator\SmallerThen',
            ]);
        }

        return static::$OperatorCollection;
    }

    /**
     * @return CriteriaBuilderFactoryWorkerInterface
     */
    protected function getFactoryWorker(): CriteriaBuilderFactoryWorkerInterface
    {
        return $this->getCriteriaBuilderFactoryWorker();
    }

    /**
     * @return void
     */
    protected function openSequence()
    {
        if ($this->isSequenceOpened) {
            return;
        }

        $this->currentContainerIndex++;
        $this->isSequenceOpened = true;
    }

    /**
     * @return void
     */
    protected function closeSequence()
    {
        $this->isSequenceOpened = false;
    }

    /**
     * @param array $criteriaParameters
     * @param array $parameters
     *
     * @return array
     */
    protected function mergeParametersDefaults(array $criteriaParameters, array $parameters): array
    {
        $tmp = [];
        foreach ($criteriaParameters as $cpValues) {
            $tmp = $this->collectionMergeDefault($tmp, $cpValues);
        }

        $parameters = $this->collectionMergeDefault($tmp, $parameters);

        return $parameters;
    }

    /**
     * @param array $sqlTokens
     * @param ContainerInterface $Container
     *
     * @return string
     */
    protected function resetGlueOnFirstIteration(array $sqlTokens, ContainerInterface $Container): string
    {
        $glue = (count($sqlTokens) === 0) ? '' : $Container->getGlue() . ' ';

        return $glue;
    }

    /**
     * @inheritdoc
     */
    public function resetParameterCollection()
    {
        $this->ParameterCollection = null;
    }

    /**
     * @inheritdoc
     */
    public function getParameterCollection(): CollectionInterface
    {
        if ($this->ParameterCollection === null) {
            $this->ParameterCollection = new Collection([]);
        }

        return $this->ParameterCollection;
    }

    /**
     * @inheritdoc
     */
    public function setParameterCollection(array $parameterCollection): CriteriaBuilderInterface
    {
        foreach ($parameterCollection as $key => $value) {
            $this->setParameter($key, $value);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setParameter($name, $value): CriteriaBuilderInterface
    {
        $name = str_replace('.', '_', $name);
        $this->getParameterCollection()->set($name, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParameter($name)
    {
        return $this->getParameterCollection()->get($name);
    }

}
