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

use Everon\Component\CriteriaBuilder\Criteria\ContainerInterface;
use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;
use Everon\Component\Factory\Exception\UnableToInstantiateException;
use Everon\Component\Factory\FactoryWorkerInterface;

interface CriteriaBuilderFactoryWorkerInterface extends FactoryWorkerInterface
{

    /**
     * @throws UnableToInstantiateException
     *
     * @return CriteriaInterface
     */
    public function buildCriteria();

    /**
     * @param string $namespace
     *
     * @throws UnableToInstantiateException
     *
     * @return CriteriaBuilderInterface
     */
    public function buildCriteriaBuilder($namespace='Everon\Component\CriteriaBuilder');

    /**
     * @param string $column
     * @param string $operator
     * @param string $value
     *
     * @throws UnableToInstantiateException
     *
     * @return CriteriumInterface
     */
    public function buildCriteriaCriterium($column, $operator, $value);

    /**
     * @param CriteriaInterface $Criteria
     * @param string $glue
     *
     * @throws UnableToInstantiateException
     *
     * @return ContainerInterface
     */
    public function buildCriteriaContainer(CriteriaInterface $Criteria, $glue);

    /**
     * @param string $class_name
     *
     * @throws UnableToInstantiateException
     *
     * @return OperatorInterface
     */
    public function buildCriteriaOperator($class_name);

    /**
     * @param string $sql
     * @param array $parameters
     *
     * @throws UnableToInstantiateException
     *
     * @return SqlPartInterface
     */
    public function buildSqlPart($sql, array $parameters);

}
