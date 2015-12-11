<?php
/**
 * This file is part of the Everon components.
 *
 * (c) Oliwier Ptak <everonphp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Criteria;

use Everon\Component\CriteriaBuilder\CriteriaBuilder;
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;
use Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException;
use Everon\Component\CriteriaBuilder\Operator\Equal;
use Everon\Component\CriteriaBuilder\Operator\Is;
use Everon\Component\CriteriaBuilder\Operator\NotEqual;
use Everon\Component\CriteriaBuilder\Operator\NotIs;
use Everon\Component\CriteriaBuilder\OperatorInterface;
use Everon\Component\CriteriaBuilder\SqlPartInterface;
use Everon\Component\CriteriaBuilder\Dependency;
use Everon\Component\Utils\Collection\ToArray;
use Everon\Component\Utils\Text\ToString;

class Criterium implements CriteriumInterface
{

    use Dependency\Setter\CriteriaBuilderFactoryWorker;

    use ToArray;
    use ToString;

    /**
     * @var string
     */
    protected $column = null;

    /**
     * @var string
     */
    protected $value = null;

    /**
     * @var string
     */
    protected $operator_type = null;

    /**
     * @var mixed
     */
    protected $placeholder = null;

    /**
     * @var string
     */
    protected $glue = null;

    /**
     * @var SqlPartInterface
     */
    protected $SqlPart = null;

    /**
     * @param $column
     * @param $value
     * @param $operator_type
     */
    public function __construct($column, $operator_type, $value)
    {
        $this->column = $column;
        $this->operator_type = $operator_type;
        $this->value = $value;
    }

    /**
     * @return array
     */
    protected function getArrayableData()
    {
        return [
            'column' => $this->getColumn(),
            'value' => $this->getValue(),
            'operator_type' => $this->getOperatorType(),
            'placeholder' => $this->getPlaceholder(),
            'glue' => $this->getGlue(),
            'SqlPart' => $this->getSqlPart(),
        ];
    }

    /**
     * @param $operator
     * @param $value
     *
     * @throws UnknownOperatorTypeException
     *
     * @return OperatorInterface
     */
    protected function buildOperatorWithValue($operator, $value)
    {
        $className = CriteriaBuilder::getOperatorClassNameBySqlOperator($operator);
        $Operator = $this->getFactoryWorker()->buildCriteriaOperator($className);

        return $this->replaceOperatorForNulLValue($Operator, $value);
    }

    /**
     * Replaces original Operators with null values by IS NULL / IS NOT NULL Operators
     *
     * @param OperatorInterface $Operator
     * @param $value
     *
     * @return OperatorInterface
     */
    protected function replaceOperatorForNulLValue(OperatorInterface $Operator, $value)
    {
        if ($value === null) {
            if ($Operator->getType() === Equal::TYPE_NAME) {
                $className = CriteriaBuilder::getOperatorClassNameBySqlOperator(Is::TYPE_AS_SQL);
                $Operator = $this->getFactoryWorker()->buildCriteriaOperator($className);
            } elseif ($Operator->getType() === NotEqual::TYPE_NAME) {
                $className = CriteriaBuilder::getOperatorClassNameBySqlOperator(NotIs::TYPE_AS_SQL);
                $Operator = $this->getFactoryWorker()->buildCriteriaOperator($className);
            }
        }

        return $Operator;
    }

    /**
     * @inheritdoc
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @inheritdoc
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function getGlue()
    {
        return $this->glue;
    }

    /**
     * @inheritdoc
     */
    public function glueByAnd()
    {
        $this->glue = CriteriaBuilder::GLUE_AND;
    }

    /**
     * @inheritdoc
     */
    public function glueByOr()
    {
        $this->glue = CriteriaBuilder::GLUE_OR;
    }

    /**
     * @inheritdoc
     */
    public function resetGlue()
    {
        $this->glue = null;
    }

    /**
     * @inheritdoc
     */
    public function getPlaceholder()
    {
        if ($this->value === null) {
            return 'NULL';
        }

        if ($this->placeholder === null) {
            $column_name = str_replace('.', '_', CriteriaBuilder::randomizeParameterName($this->getColumn()));
            $this->placeholder = ':' . $column_name;
        }

        return $this->placeholder;
    }

    /**
     * @inheritdoc
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @inheritdoc
     */
    public function getPlaceholderAsParameter()
    {
        return ltrim($this->getPlaceholder(), ':');
    }

    /**
     * @inheritdoc
     */
    public function getOperatorType()
    {
        return $this->operator_type;
    }

    /**
     * @inheritdoc
     */
    public function setOperatorType($operator)
    {
        $this->operator_type = $operator;
    }

    /**
     * @inheritdoc
     */
    public function setSqlPart(SqlPartInterface $SqlPart)
    {
        $this->SqlPart = $SqlPart;
    }

    /**
     * @inheritdoc
     */
    public function getSqlPart()
    {
        if ($this->SqlPart === null) {
            $Operator = $this->buildOperatorWithValue($this->getOperatorType(), $this->getValue());
            list($sql, $parameters) = $Operator->toSqlPartData($this);
            $this->SqlPart = $this->getFactoryWorker()->buildSqlPart($sql, $parameters);
        }

        return $this->SqlPart;
    }

    /**
     * @return CriteriaBuilderFactoryWorkerInterface
     */
    protected function getFactoryWorker()
    {
        return $this->getCriteriaBuilderFactoryWorker();
    }

}
