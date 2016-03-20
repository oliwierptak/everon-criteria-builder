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

use Everon\Component\CriteriaBuilder\Criteria\Container;
use Everon\Component\CriteriaBuilder\Criteria\Criterium;
use Everon\Component\Factory\AbstractWorker;

class CriteriaBuilderFactoryWorker extends AbstractWorker implements CriteriaBuilderFactoryWorkerInterface
{

    /**
     * @inheritdoc
     */
    public function buildCriteria()
    {
        $Criteria = new Criteria();
        $this->getFactory()->injectDependencies(Criteria::class, $Criteria);

        return $Criteria;
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaBuilder()
    {
        $CriteriaBuilder = new CriteriaBuilder();
        $this->getFactory()->injectDependencies(CriteriaBuilder::class, $CriteriaBuilder);

        return $CriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaCriterium($column, $operator, $value)
    {
        $Criterium = new Criterium($column, $operator, $value);
        $this->getFactory()->injectDependencies(Criterium::class, $Criterium);

        return $Criterium;
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaContainer(CriteriaInterface $Criteria, $glue)
    {
        $Container = new Container($Criteria, $glue);
        $this->getFactory()->injectDependencies(Container::class, $Container);

        return $Container;
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaOperator($class_name)
    {
        return new $class_name();
    }

    /**
     * @inheritdoc
     */
    public function buildSqlPart($sql, array $parameters)
    {
        $SqlPart = new SqlPart($sql, $parameters);
        $this->getFactory()->injectDependencies(SqlPart::class, $SqlPart);

        return $SqlPart;
    }

}
