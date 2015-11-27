<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <EveronFramework@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder;

use Everon\Component\Factory\AbstractWorker;
use Everon\Component\Factory\Exception\UnableToInstantiateException;

class CriteriaBuilderFactoryWorker extends AbstractWorker implements CriteriaBuilderFactoryWorkerInterface
{
    /**
     * @inheritdoc
     */
    protected function registerBeforeWork()
    {
        $Factory = $this->getFactory();
        $this->getFactory()->getDependencyContainer()->propose('CriteriaBuilderFactoryWorker', function() use ($Factory) {
            return $Factory->getWorkerByName('CriteriaBuilder', 'Everon\Component\CriteriaBuilder');
        });
    }

    /**
     * @inheritdoc
     */
    public function buildCriteria($namespace='Everon\Component\CriteriaBuilder')
    {
        try {
            $class_name = $this->getFactory()->getFullClassName($namespace, 'Criteria');
            $this->getFactory()->classExists($class_name);
            $Criteria = new $class_name();
            $this->getFactory()->injectDependencies($class_name, $Criteria);
            return $Criteria;
        }
        catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaBuilder($namespace='Everon\Component\CriteriaBuilder')
    {
        try {
            $class_name = $this->getFactory()->getFullClassName($namespace, 'Builder');
            $this->getFactory()->classExists($class_name);
            $Builder = new $class_name();
            $this->getFactory()->injectDependencies($class_name, $Builder);
            return $Builder;
        }
        catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaCriterium($column, $operator, $value, $namespace = 'Everon\Component\CriteriaBuilder\Criteria')
    {
        try {
            $class_name = $this->getFactory()->getFullClassName($namespace, 'Criterium');
            $this->getFactory()->classExists($class_name);
            $Criterium = new $class_name($column, $operator, $value);
            $this->getFactory()->injectDependencies($class_name, $Criterium);
            return $Criterium;
        }
        catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaContainer(CriteriaInterface $Criteria, $glue, $namespace='Everon\Component\CriteriaBuilder\Criteria')
    {
        try {
            $class_name = $this->getFactory()->getFullClassName($namespace, 'Container');
            $this->getFactory()->classExists($class_name);
            $Container = new $class_name($Criteria, $glue);
            $this->getFactory()->injectDependencies($class_name, $Container);
            return $Container;
        }
        catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaOperator($class_name)
    {
        try {
            $this->getFactory()->classExists($class_name);
            $CriteriaOperator = new $class_name();
            $this->getFactory()->injectDependencies($class_name, $CriteriaOperator);
            return $CriteriaOperator;
        }
        catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildSqlPart($sql, array $parameters, $namespace = 'Everon\Component\CriteriaBuilder')
    {
        try {
            $class_name = $this->getFactory()->getFullClassName($namespace, 'SqlPart');
            $this->getFactory()->classExists($class_name);
            $SqlPart = new $class_name($sql, $parameters);
            $this->getFactory()->injectDependencies($class_name, $SqlPart);
            return $SqlPart;
        }
        catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

}
