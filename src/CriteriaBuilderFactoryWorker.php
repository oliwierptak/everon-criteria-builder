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
        $this->getFactory()->getDependencyContainer()->propose('CriteriaBuilderFactoryWorker', function () {
            return $this->getFactory()->getWorkerByName('CriteriaBuilder', 'Everon\Component\CriteriaBuilder');
        });
    }

    /**
     * @inheritdoc
     */
    public function buildCriteria($namespace='Everon\Component\CriteriaBuilder')
    {
        try {
            return $this->getFactory()->buildWithEmptyConstructor('Criteria', $namespace);
        } catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaBuilder($namespace='Everon\Component\CriteriaBuilder')
    {
        try {
            return $this->getFactory()->buildWithEmptyConstructor('Builder', $namespace);
        } catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaCriterium($column, $operator, $value, $namespace = 'Everon\Component\CriteriaBuilder\Criteria')
    {
        try {
            return $this->getFactory()->buildWithConstructorParameters('Criterium', $namespace,
                $this->getFactory()->buildParameterCollection([
                    $column, $operator, $value,
                ])
            );
        } catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaContainer(CriteriaInterface $Criteria, $glue, $namespace='Everon\Component\CriteriaBuilder\Criteria')
    {
        try {
            return $this->getFactory()->buildWithConstructorParameters('Container', $namespace,
                $this->getFactory()->buildParameterCollection([
                    $Criteria, $glue,
                ])
            );
        } catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildCriteriaOperator($class_name)
    {
        try {
            return $this->getFactory()->buildWithEmptyConstructor($class_name, '');
        } catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildSqlPart($sql, array $parameters, $namespace = 'Everon\Component\CriteriaBuilder')
    {
        try {
            return $this->getFactory()->buildWithConstructorParameters('SqlPart', $namespace,
                $this->getFactory()->buildParameterCollection([
                    $sql, $parameters,
                ])
            );
        } catch (\Exception $e) {
            throw new UnableToInstantiateException($e);
        }
    }

}
