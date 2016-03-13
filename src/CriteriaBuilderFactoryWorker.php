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
use Everon\Component\Factory\Exception\UnableToInstantiateException;

class CriteriaBuilderFactoryWorker extends AbstractWorker implements CriteriaBuilderFactoryWorkerInterface
{

    /**
     * @inheritdoc
     */
    public function buildCriteria()
    {
        try {
            $Criteria = new Criteria();
            $this->getFactory()->injectDependencies(Criteria::class, $Criteria);

            return $Criteria;
        } catch (\Exception $e) {
            throw new UnableToInstantiateException(Criteria::class, null, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaBuilder($namespace='Everon\Component\CriteriaBuilder')
    {
        try {
            $CriteriaBuilder = new CriteriaBuilder();
            $this->getFactory()->injectDependencies(CriteriaBuilder::class, $CriteriaBuilder);

            return $CriteriaBuilder;
        } catch (\Exception $e) {
            throw new UnableToInstantiateException(CriteriaBuilder::class, null, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaCriterium($column, $operator, $value)
    {
        try {
            $Criterium = new Criterium($column, $operator, $value);
            $this->getFactory()->injectDependencies(Criterium::class, $Criterium);

            return $Criterium;
        } catch (\Exception $e) {
            throw new UnableToInstantiateException(Criterium::class, null, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaContainer(CriteriaInterface $Criteria, $glue)
    {
        try {
            $Container = new Container($Criteria, $glue);
            $this->getFactory()->injectDependencies(Container::class, $Container);

            return $Container;
        } catch (\Exception $e) {
            throw new UnableToInstantiateException(Container::class, null, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaOperator($class_name)
    {
        try {
            return new $class_name();
        } catch (\Exception $e) {
            throw new UnableToInstantiateException($class_name, null, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildSqlPart($sql, array $parameters)
    {
        try {
            $SqlPart = new SqlPart($sql, $parameters);
            $this->getFactory()->injectDependencies(SqlPart::class, $SqlPart);

            return $SqlPart;
        } catch (\Exception $e) {
            throw new UnableToInstantiateException(SqlPart::class, null, $e);
        }
    }

}
