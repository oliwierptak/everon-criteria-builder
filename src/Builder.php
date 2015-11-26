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

use Everon\Component\Collection\Collection;
use Everon\Component\Collection\CollectionInterface;
use Everon\Component\CriteriaBuilder\Criteria\ContainerInterface;
use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;
use Everon\Component\CriteriaBuilder\Operator;
use Everon\Component\CriteriaBuilder\Dependency;
use Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException;
use Everon\Component\Utils\Collection\MergeDefault;
use Everon\Component\Utils\Collection\ToArray;
use Everon\Component\Utils\Text\ToString;


class Builder implements BuilderInterface
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
    protected static $operatorCollection = null;
    
    /**
     * @var string
     */
    protected $current = -1;

    /**
     * @var CollectionInterface
     */
    protected $ContainerCollection = null;

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
    protected $order_by = [];

    /**
     * @var string
     */
    protected $group_by = null;

    
    public function __construct()
    {
        $this->ContainerCollection = new Collection([]);
    }

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
     * @return string
     */
    protected function criteriaToSql(ContainerInterface $Container)
    {
        /**
         * @var CriteriumInterface $Criterium
         */
        $sql = '';
        foreach ($Container->getCriteria()->getCriteriumCollection() as $Criterium) {
            $SqlPart = $Criterium->getSqlPart();
            $sql .= ltrim($Criterium->getGlue().' '.$SqlPart->getSql().' ');
        }

        return '('.rtrim($sql).')';
    }

    /**
     * @param ContainerInterface $Container
     * @return array
     */
    protected function criteriaToParameters(ContainerInterface $Container)
    {
        /**
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
        $this->current++;
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($column, $operator, $value);
        $this->getCurrentContainer()->getCriteria()->where($Criterium);

        if ($this->current > 0) {
            $this->getCurrentContainer()->setGlue($glue);
        }
        else {
            $this->getCurrentContainer()->resetGlue(); //reset glue for first item
        }
        
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function andWhere($column, $operator, $value)
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($column, $operator, $value);
        if ($this->current < 0) {
            $this->where($column, $operator, $value);
        }
        else {
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
        if ($this->current < 0) {
            $this->where($column, $operator, $value);
        }
        else {
            $this->getCurrentContainer()->getCriteria()->orWhere($Criterium);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function whereRaw($sql, array $value = null, $glue = self::GLUE_AND)
    {
        $this->current++;
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($sql, 'raw', $value);
        $this->getCurrentContainer()->getCriteria()->where($Criterium);

        if ($this->current > 0) {
            $this->getCurrentContainer()->setGlue($glue);
        }
        else {
            $this->getCurrentContainer()->resetGlue(); //reset glue for first item
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function andWhereRaw($sql, array $value = null)
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($sql, 'raw', $value);
        if ($this->current < 0) {
            $this->where($sql, 'raw', $value);
        }
        else {
            $this->getCurrentContainer()->getCriteria()->andWhere($Criterium);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function orWhereRaw($sql, array $value = null)
    {
        $Criterium = $this->getFactoryWorker()->buildCriteriaCriterium($sql, 'raw', $value);
        if ($this->current < 0) {
            $this->where($sql, 'raw', $value);
        }
        else {
            $this->getCurrentContainer()->getCriteria()->orWhere($Criterium);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentContainer()
    {
        if ($this->ContainerCollection->has($this->current) === false) {
            $Criteria = $this->getFactoryWorker()->buildCriteria();
            $Container = $this->getFactoryWorker()->buildCriteriaContainer($Criteria, null);
            $this->ContainerCollection[$this->current] = $Container;
        }

        return $this->ContainerCollection[$this->current];
    }

    /**
     * @inheritdoc
     */
    public function setCurrentContainer(ContainerInterface $Container)
    {
        $this->ContainerCollection[$this->current] = $Container;
    }

    /**
     * @inheritdoc
     */
    public function getContainerCollection()
    {
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
        $this->getCurrentContainer()->resetGlue();
    }

    /**
     * @inheritdoc
     */
    public function glueByAnd()
    {
        $this->getCurrentContainer()->glueByAnd();
    }

    /**
     * @inheritdoc
     */
    public function glueByOr()
    {
        $this->getCurrentContainer()->glueByOr();
    }

    /**
     * @inheritdoc
     */
    public function getGroupBy()
    {
        return $this->group_by;
    }

    /**
     * @inheritdoc
     */
    public function setGroupBy($group_by)
    {
        $this->group_by = $group_by;
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
        return $this->order_by;
    }

    /**
     * @inheritdoc
     */
    public function setOrderBy(array $order_by)
    {
        $this->order_by = $order_by;
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
            return 'OFFSET '.$this->offset;
        }

        if ((int) $this->getLimit() !== 0 && $this->getOffset() === null) {
            return 'LIMIT '.$this->getLimit();
        }

        return 'LIMIT '.$this->getLimit(). ' OFFSET '.$this->getOffset();
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
            $order_by .= "${name} ".$sort.',';
        }

        if ($order_by !== '') {
            $order_by = trim($order_by, ',');
            $order_by = 'ORDER BY '.$order_by;
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

        return 'GROUP BY '.$this->getGroupBy();
    }

    /**
     * @inheritdoc
     */
    public function toSqlPart()
    {
        $sql = [];
        $parameters = [];
        $glue = null;

        foreach ($this->getContainerCollection() as $Container) {
            $glue = (count($sql) === 0) ? '' : $Container->getGlue().' '; //reset glue if that's the first iteration

            $sql[] = $glue.$this->criteriaToSql($Container);
            $criteria_parameters = $this->criteriaToParameters($Container);
            $tmp = [];

            foreach ($criteria_parameters as $cp_value) {
                $tmp = $this->collectionMergeDefault($tmp, $cp_value);
            }

            $parameters = $this->collectionMergeDefault($tmp, $parameters);
        }

        $sql_query = implode("\n", $sql);
        $sql_query = rtrim($sql_query, $glue.' ');

        $sql_query .= ' '.trim($this->getGroupBySql().' '.
                $this->getOrderByAndSortSql().' '.
                $this->getOffsetLimitSql());

        $sql_query = empty($sql) === false ? 'WHERE '.$sql_query : $sql_query;

        return $this->getFactoryWorker()->buildSqlPart(trim($sql_query), $parameters);
    }

    /**
     * @inheritdoc
     */
    public function appendContainerCollection(CollectionInterface $ContainerCollectionToMerge, $glue=self::GLUE_AND)
    {
        /**
         * @var ContainerInterface $ContainerToMerge
         */
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
    public static function getOperatorClassNameBySqlOperator($operator)
    {
        $operator = strtoupper(trim($operator));
        if (static::getOperatorCollection()->has($operator) === false) {
            throw new UnknownOperatorTypeException($operator);
        }
        
        return static::getOperatorCollection()->get($operator);
    }

    /**
     * @inheritdoc
     */
    public static function randomizeParameterName($name)
    {
        return $name.'_'.mt_rand(100, time());
    }

    /**
     * @return CollectionInterface
     */
    public static function getOperatorCollection()
    {
        if (static::$operatorCollection === null) {
            static::$operatorCollection = new Collection([
                Operator\Between::TYPE_AS_SQL => 'Between',
                Operator\Equal::TYPE_AS_SQL => 'Equal',
                Operator\GreaterOrEqual::TYPE_AS_SQL => 'GreaterOrEqual',
                Operator\GreaterThen::TYPE_AS_SQL => 'GreaterThen',
                Operator\Ilike::TYPE_AS_SQL => 'Ilike',
                Operator\In::TYPE_AS_SQL => 'In',
                Operator\Is::TYPE_AS_SQL => 'Is',
                Operator\Like::TYPE_AS_SQL => 'Like',
                Operator\NotBetween::TYPE_AS_SQL => 'NotBetween',
                Operator\NotEqual::TYPE_AS_SQL => 'NotEqual',
                Operator\NotIlike::TYPE_AS_SQL => 'NotIlike',
                Operator\NotIn::TYPE_AS_SQL => 'NotIn',
                Operator\NotIn::TYPE_AS_SQL => 'NotIn',
                Operator\NotIs::TYPE_AS_SQL => 'NotIs',
                Operator\NotLike::TYPE_AS_SQL => 'NotLike',
                Operator\Raw::TYPE_AS_SQL => 'Raw',
                Operator\SmallerOrEqual::TYPE_AS_SQL => 'SmallerOrEqual',
                Operator\SmallerThen::TYPE_AS_SQL => 'SmallerThen',
            ]);
        }

        return static::$operatorCollection;
    }

    /**
     * @return CriteriaBuilderFactoryWorkerInterface
     */
    protected function getFactoryWorker()
    {
        return $this->getCriteriaBuilderFactoryWorker();
    }
    
}
