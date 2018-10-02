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
     * @var CollectionInterface|null
     */
    protected static $OperatorCollection;

    /**
     * @var int
     */
    protected $currentContainerIndex = -1;

    /**
     * @var CollectionInterface|null
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
     * @var CollectionInterface|null
     */
    protected $ParameterCollection;

    /**
     * @return array
     */
    protected function getArrayableData()
    {
        $SqlPart = $this->toSqlPart();

        return $SqlPart->getParameters();
    }

    /**
     * @return string
     */
    protected function getToString()
    {
        $SqlPart = $this->toSqlPart();

        return $SqlPart->getSql();
    }

    /**
     * @param ContainerInterface $Container
     *
     * @return string
     */
    protected function criteriaToSql(ContainerInterface $Container)
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
    protected function criteriaToParameters(ContainerInterface $Container)
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
    public function where($column, $operator, $value, $glue = self::GLUE_AND)
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
    public function andWhere($column, $operator, $value)
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
    public function orWhere($column, $operator, $value)
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
    public function whereRaw($sql, array $value = null, $customType = 'raw', $glue = self::GLUE_AND)
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
    public function andWhereRaw($sql, array $value = null, $customType = 'raw')
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
    public function orWhereRaw($sql, array $value = null, $customType = 'raw')
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
    public function getCurrentContainer()
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
    public function getContainerCollection()
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
    public function resetGlue()
    {
        $this->openSequence();
        $this->getCurrentContainer()->resetGlue();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function glueByAnd()
    {
        $this->openSequence();
        $this->getCurrentContainer()->glueByAnd();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function glueByOr()
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
    public function setGroupBy($groupBy)
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
    public function setLimit($limit)
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
    public function setOffset($offset)
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
    public function setOrderBy(array $orderBy)
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSqlTemplate()
    {
        return $this->sqlTemplate;
    }

    /**
     * @inheritdoc
     */
    public function sql($sqlTemplate)
    {
        $this->sqlTemplate = $sqlTemplate;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOffsetLimitSql()
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
    public function getOrderByAndSortSql()
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
    public function getGroupBySql()
    {
        if ($this->getGroupBy() === null) {
            return '';
        }

        return 'GROUP BY ' . $this->getGroupBy();
    }

    /**
     * @inheritdoc
     */
    public function toSqlPart()
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
     * @param $glue
     *
     * @return string
     */
    protected function formatSqlQuery(array $sqlTokens, $glue)
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
    protected function hasCustomSqlTemplate()
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
    public static function getOperatorClassNameBySqlOperator($sqlOperator)
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
    public static function randomizeParameterName($name)
    {
        return $name . '_' . mt_rand(100, time());
    }

    /**
     * @inheritdoc
     */
    public static function getOperatorCollection()
    {
        if (static::$OperatorCollection === null) {
            static::$OperatorCollection = new Collection([
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
            ]);
        }

        return static::$OperatorCollection;
    }

    /**
     * @return CriteriaBuilderFactoryWorkerInterface
     */
    protected function getFactoryWorker()
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
    protected function mergeParametersDefaults(array $criteriaParameters, array $parameters)
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
    protected function resetGlueOnFirstIteration(array $sqlTokens, ContainerInterface $Container)
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
    public function getParameterCollection()
    {
        if ($this->ParameterCollection === null) {
            $this->ParameterCollection = new Collection([]);
        }

        return $this->ParameterCollection;
    }

    /**
     * @inheritdoc
     */
    public function setParameterCollection(array $parameterCollection)
    {
        foreach ($parameterCollection as $key => $value) {
            $this->setParameter($key, $value);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setParameter($name, $value)
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
