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

use Everon\Component\CriteriaBuilder\Criteria\Container;
use Everon\Component\CriteriaBuilder\Criteria\ContainerInterface;
use Everon\Component\CriteriaBuilder\Criteria\Criterium;
use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;
use Everon\Component\Factory\AbstractWorker;

class CriteriaBuilderFactoryWorker extends AbstractWorker implements CriteriaBuilderFactoryWorkerInterface
{
    public function buildCriteria(): CriteriaInterface
    {
        $Criteria = new Criteria();
        $this->getFactory()->injectDependencies(Criteria::class, $Criteria);

        return $Criteria;
    }

    public function buildCriteriaBuilder(): CriteriaBuilderInterface
    {
        $CriteriaBuilder = new CriteriaBuilder();
        $this->getFactory()->injectDependencies(CriteriaBuilder::class, $CriteriaBuilder);

        return $CriteriaBuilder;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param array|string|null $value
     *
     * @return \Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     */
    public function buildCriteriaCriterium(
        string $column,
        string $operator,
        $value
    ): CriteriumInterface {
        $Criterium = new Criterium($column, $operator, $value);
        $this->getFactory()->injectDependencies(Criterium::class, $Criterium);

        return $Criterium;
    }

    public function buildCriteriaContainer(CriteriaInterface $Criteria, ?string $glue): ContainerInterface
    {
        $Container = new Container($Criteria, $glue);
        $this->getFactory()->injectDependencies(Container::class, $Container);

        return $Container;
    }

    public function buildCriteriaOperator(string $className): OperatorInterface
    {
        return new $className();
    }

    /**
     * @param string $sql
     * @param array $parameters
     *
     * @return \Everon\Component\CriteriaBuilder\SqlPartInterface
     * @throws \Everon\Component\Factory\Exception\FailedToInjectDependenciesException
     */
    public function buildSqlPart(string $sql, array $parameters): SqlPartInterface
    {
        $SqlPart = new SqlPart($sql, $parameters);
        $this->getFactory()->injectDependencies(SqlPart::class, $SqlPart);

        return $SqlPart;
    }

}
