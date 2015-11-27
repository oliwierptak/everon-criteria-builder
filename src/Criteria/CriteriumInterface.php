<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <EveronFramework@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Criteria;

use Everon\Component\CriteriaBuilder\Dependency\CriteriaBuilderFactoryWorkerDependencyInterface;
use Everon\Component\CriteriaBuilder\SqlPartInterface;
use Everon\Component\Utils\Collection\ArrayableInterface;

interface CriteriumInterface extends ArrayableInterface, CriteriaBuilderFactoryWorkerDependencyInterface
{

    /**
     * @return string
     */
    public function getColumn();

    /**
     * @param string $column
     */
    public function setColumn($column);

    /**
     * @return string
     */
    public function getOperatorType();

    /**
     * @param string
     */
    public function setOperatorType($operator);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param string $value
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getGlue();

    /**
     * @return void
     */
    public function glueByAnd();

    /**
     * @return void
     */
    public function glueByOr();

    /**
     * @return void
     */
    public function resetGlue();

    /**
     * @return mixed
     */
    public function getPlaceholder();

    /**
     * @param mixed $placeholder
     */
    public function setPlaceholder($placeholder);

    /**
     * @return string
     */
    public function getPlaceholderAsParameter();

    /**
     * @return SqlPartInterface
     */
    public function getSqlPart();

    /**
     * @param SqlPartInterface $SqlPart
     */
    public function setSqlPart(SqlPartInterface $SqlPart);

}
