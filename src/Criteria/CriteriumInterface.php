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
    function getColumn();

    /**
     * @param string $column
     */
    function setColumn($column);

    /**
     * @return string
     */
    function getOperatorType();

    /**
     * @param string
     */
    function setOperatorType($operator);

    /**
     * @return string
     */
    function getValue();

    /**
     * @param string $value
     */
    function setValue($value);

    /**
     * @return string
     */
    function getGlue();

    function glueByAnd();

    function glueByOr();

    function resetGlue();

    /**
     * @return mixed
     */
    function getPlaceholder();

    /**
     * @param mixed $placeholder
     */
    function setPlaceholder($placeholder);

    /**
     * @return string
     */
    function getPlaceholderAsParameter();

    /**
     * @return SqlPartInterface
     */
    function getSqlPart();

    /**
     * @param SqlPartInterface $SqlPart
     */
    function setSqlPart(SqlPartInterface $SqlPart);
}
