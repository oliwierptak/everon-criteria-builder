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

use Everon\Component\CriteriaBuilder\Criteria\ContainerInterface;
use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;
use Everon\Component\Factory\FactoryWorkerInterface;

interface CriteriaBuilderFactoryWorkerInterface extends FactoryWorkerInterface
{
    /**
     * @return \Everon\Component\CriteriaBuilder\CriteriaInterface
     */
    public function buildCriteria(): CriteriaInterface;

    /**
     * @return \Everon\Component\CriteriaBuilder\CriteriaBuilderInterface
     */
    public function buildCriteriaBuilder(): CriteriaBuilderInterface;

    /**
     * @param string $column
     * @param string $operator
     * @param array|string|null $value
     *
     * @return \Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface
     */
    public function buildCriteriaCriterium(
        string $column,
        string $operator,
        $value
    ): CriteriumInterface;

    /**
     * @param \Everon\Component\CriteriaBuilder\CriteriaInterface $Criteria
     * @param string|null $glue
     *
     * @return \Everon\Component\CriteriaBuilder\Criteria\ContainerInterface
     */
    public function buildCriteriaContainer(CriteriaInterface $Criteria, ?string $glue): ContainerInterface;

    public function buildCriteriaOperator(string $className): OperatorInterface;

    /**
     * @param string $sql
     * @param array $parameters
     *
     * @return \Everon\Component\CriteriaBuilder\SqlPartInterface
     */
    public function buildSqlPart(string $sql, array $parameters): SqlPartInterface;
}
