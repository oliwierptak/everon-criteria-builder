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
     * @param string $namespace
     *
     * @throws UnableToInstantiateException
     *
     * @return CriteriaInterface
     */
    public function buildCriteria($namespace='Everon\Component\CriteriaBuilder'): CriteriaInterface;

    /**
     * @param string $namespace
     *
     * @throws UnableToInstantiateException
     *
     * @return CriteriaBuilderInterface
     */
    public function buildCriteriaBuilder($namespace='Everon\Component\CriteriaBuilder'): CriteriaBuilderInterface;

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @param string $namespace
     *
     * @throws UnableToInstantiateException
     *
     * @return CriteriumInterface
     */
    public function buildCriteriaCriterium($column, $operator, $value, $namespace = 'Everon\Component\CriteriaBuilder\Criteria'): CriteriumInterface;

    /**
     * @param CriteriaInterface $Criteria
     * @param $glue
     * @param string $namespace
     *
     * @throws UnableToInstantiateException
     *
     * @return ContainerInterface
     */
    public function buildCriteriaContainer(CriteriaInterface $Criteria, $glue, $namespace='Everon\Component\CriteriaBuilder\Criteria'): ContainerInterface;

    /**
     * @param $class_name
     *
     * @throws UnableToInstantiateException
     *
     * @return OperatorInterface
     */
    public function buildCriteriaOperator($class_name): OperatorInterface;

    /**
     * @param $sql
     * @param array $parameters
     * @param string $namespace
     *
     * @throws UnableToInstantiateException
     *
     * @return SqlPartInterface
     */
    public function buildSqlPart($sql, array $parameters, $namespace = 'Everon\Component\CriteriaBuilder'): SqlPartInterface;

}
