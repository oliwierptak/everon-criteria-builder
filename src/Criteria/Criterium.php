<?php declare(strict_types = 1);
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
     * @var mixed
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
     * @param string $column
     * @param string $operator_type
     * @param mixed $value
     */
    public function __construct(string $column, string $operator_type, $value)
    {
        $this->column = $column;
        $this->operator_type = $operator_type;
        $this->value = $value;
    }

    protected function getArrayableData(): array
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
     * @param string $operator
     * @param mixed $value
     *
     * @return \Everon\Component\CriteriaBuilder\OperatorInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException
     */
    protected function buildOperatorWithValue(string $operator, $value): OperatorInterface
    {
        $className = CriteriaBuilder::getOperatorClassNameBySqlOperator($operator);
        $Operator = $this->getFactoryWorker()->buildCriteriaOperator($className);

        return $this->replaceOperatorForNulLValue($Operator, $value);
    }

    /**
     * Replaces original Operators with null values by IS NULL / IS NOT NULL Operators
     *
     * @param \Everon\Component\CriteriaBuilder\OperatorInterface $Operator
     * @param mixed $value
     *
     * @return \Everon\Component\CriteriaBuilder\OperatorInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException
     */
    protected function replaceOperatorForNulLValue(OperatorInterface $Operator, $value): OperatorInterface
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

    public function getColumn(): string
    {
        return $this->column;
    }

    public function setColumn(string $column)
    {
        $this->column = $column;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getGlue(): ?string
    {
        return $this->glue;
    }

    public function glueByAnd(): void
    {
        $this->glue = CriteriaBuilder::GLUE_AND;
    }

    public function glueByOr(): void
    {
        $this->glue = CriteriaBuilder::GLUE_OR;
    }

    public function resetGlue(): void
    {
        $this->glue = null;
    }

    public function getPlaceholder(): string
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

    public function setPlaceholder(string $placeholder)
    {
        $this->placeholder = $placeholder;
    }

    public function getPlaceholderAsParameter(): string
    {
        return ltrim($this->getPlaceholder(), ':');
    }

    public function getOperatorType(): string
    {
        return $this->operator_type;
    }

    public function setOperatorType(string $operator)
    {
        $this->operator_type = $operator;
    }

    public function setSqlPart(SqlPartInterface $SqlPart)
    {
        $this->SqlPart = $SqlPart;
    }

    public function getSqlPart(): SqlPartInterface
    {
        if ($this->SqlPart === null) {
            $Operator = $this->buildOperatorWithValue($this->getOperatorType(), $this->getValue());
            [$sql, $parameters] = $Operator->toSqlPartData($this);
            $this->SqlPart = $this->getFactoryWorker()->buildSqlPart($sql, $parameters);
        }

        return $this->SqlPart;
    }

    protected function getFactoryWorker(): CriteriaBuilderFactoryWorkerInterface
    {
        return $this->getCriteriaBuilderFactoryWorker();
    }

}
