<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <EveronFramework@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Tests\Unit;

use Everon\Component\CriteriaBuilder\Builder;
use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;
use Everon\Component\CriteriaBuilder\CriteriaInterface;
use Everon\Component\CriteriaBuilder\Operator\Equal;
use Everon\Component\CriteriaBuilder\OperatorInterface;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\Factory\Dependency\ContainerInterface;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\Utils\TestCase\MockeryTest;
use Everon\Component\Utils\Text\StartsWith;
use Mockery;
use Mockery\MockInterface;
use Everon\Component\Factory\Tests\Unit\Doubles\FactoryStub;

class CriteriaTest extends MockeryTest
{
    use StartsWith;

    /**
     * @var CriteriaBuilderFactoryWorkerInterface
     */
    protected $CriteriaBuilderFactoryWorker;

    /**
     * @var FactoryInterface
     */
    protected $Factory;

    protected function setUp()
    {
        $Container = Mockery::mock('Everon\Component\Factory\Dependency\ContainerInterface');
        $Container = new Container();

        /** @var ContainerInterface $Container */
        $this->Factory = new FactoryStub($Container);
        $this->CriteriaBuilderFactoryWorker = $this->Factory->getWorkerByName('CriteriaBuilder', 'Everon\Component\CriteriaBuilder');

        $FactoryWorker = $this->CriteriaBuilderFactoryWorker;

        $Container->register('CriteriaBuilderFactoryWorker', function() use ($FactoryWorker) {
            return $FactoryWorker;
        });

    }

    public function test_Criteria()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $Criteria);
    }

    public function test_Criteria_to_where()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium(
            'foo',
            '=',
            'bar'
        );

        $Criteria->where($Criterium);

        $this->assertInstanceOf('Everon\Component\Collection\CollectionInterface', $Criteria->getCriteriumCollection());
        $this->assertEquals(1, $Criteria->getCriteriumCollection()->count());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', current($Criteria->getCriteriumCollection()->toArray()));
        $this->assertInternalType('array', $Criteria->toArray());
    }

    public function test_Criteria_to_where_and_where()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium(
            'foo',
            '=',
            'bar'
        );

        $CriteriumAnd = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium(
            'fuzz',
            '>=',
            123
        );

        $Criteria->where($Criterium)
            ->andWhere($CriteriumAnd);

        $array = $Criteria->toArray();

        $this->assertInstanceOf('Everon\Component\Collection\CollectionInterface', $Criteria->getCriteriumCollection());
        $this->assertEquals(2, $Criteria->getCriteriumCollection()->count());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', current($Criteria->getCriteriumCollection()->toArray()));
        $this->assertInternalType('array', $array);

        /** @var CriteriumInterface $AndCriterium */
        /** @var CriteriumInterface $AndCriteriumSecond */
        list($AndCriterium, $AndCriteriumSecond) = $array;
        $this->assertNull($AndCriterium->getGlue());
        $this->assertEquals(Builder::GLUE_AND, $AndCriteriumSecond->getGlue());
    }

    public function test_Criteria_to_where_or_where()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium(
            'foo',
            '=',
            'bar'
        );

        $CriteriumAnd = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium(
            'fuzz',
            '>=',
            123
        );

        $Criteria->where($Criterium)
            ->orWhere($CriteriumAnd);

        $array = $Criteria->toArray();

        $this->assertInstanceOf('Everon\Component\Collection\CollectionInterface', $Criteria->getCriteriumCollection());
        $this->assertEquals(2, $Criteria->getCriteriumCollection()->count());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', current($Criteria->getCriteriumCollection()->toArray()));
        $this->assertInternalType('array', $array);

        /** @var CriteriumInterface $AndCriterium */
        /** @var CriteriumInterface $OrCriterium */
        list($AndCriterium, $OrCriterium) = $array;
        $this->assertNull($AndCriterium->getGlue());
        $this->assertEquals(Builder::GLUE_OR, $OrCriterium->getGlue());
    }

    public function test_Criteria_to_array()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium(
            'foo',
            '=',
            'bar'
        );

        $Criteria->where($Criterium);

        $this->assertInternalType('array', $Criteria->toArray());
    }

}
