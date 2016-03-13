<?php
/**
 * This file is part of the Everon components.
 *
 * (c) Oliwier Ptak <everonphp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Tests\Unit;

use Everon\Component\CriteriaBuilder\CriteriaBuilder;
use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorker;
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\CriteriaBuilder\Tests\Unit\Doubles\FactoryStub;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\Utils\TestCase\MockeryTest;
use Everon\Component\Utils\Text\StartsWith;

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
        $Container = new Container();
        $this->Factory = new FactoryStub($Container);
        $this->Factory->registerWorkerCallback('CriteriaBuilderFactoryWorker', function () {
            return $this->Factory->buildWorker(CriteriaBuilderFactoryWorker::class);
        });
        $this->CriteriaBuilderFactoryWorker = $this->Factory->getWorkerByName('CriteriaBuilderFactoryWorker');
    }

    public function test_Constructor()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $Criteria);
    }

    public function test_where()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $Criteria->where($Criterium);

        $this->assertInstanceOf('Everon\Component\Collection\CollectionInterface', $Criteria->getCriteriumCollection());
        $this->assertEquals(1, $Criteria->getCriteriumCollection()->count());

        $CriteriumCollection = $Criteria->getCriteriumCollection()->toArray();
        array_walk($CriteriumCollection, function ($Criterium) {
            $this->assertInstanceOf(
                'Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface',
                $Criterium
            );
        });

        $this->assertInternalType('array', $Criteria->toArray());
    }

    public function test_where_and_where()
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
        $this->assertInternalType('array', $array);

        /** @var CriteriumInterface $AndCriterium */
        /** @var CriteriumInterface $AndCriteriumSecond */
        list($AndCriterium, $AndCriteriumSecond) = $array;

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', $AndCriterium);
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', $AndCriteriumSecond);

        $this->assertNull($AndCriterium->getGlue());
        $this->assertEquals(CriteriaBuilder::GLUE_AND, $AndCriteriumSecond->getGlue());
    }

    public function test_where_or_where()
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
        $this->assertInternalType('array', $array);

        /** @var CriteriumInterface $AndCriterium */
        /** @var CriteriumInterface $OrCriterium */
        list($AndCriterium, $OrCriterium) = $array;

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', $AndCriterium);
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', $OrCriterium);

        $this->assertNull($AndCriterium->getGlue());
        $this->assertEquals(CriteriaBuilder::GLUE_OR, $OrCriterium->getGlue());
    }

    public function test_to_array()
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

    public function test_glue_by_and()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $Criteria->glueByAnd();

        $this->assertEquals(CriteriaBuilder::GLUE_AND, $Criteria->getGlue());
    }

    public function test_glue_by_or()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $Criteria->glueByOr();

        $this->assertEquals(CriteriaBuilder::GLUE_OR, $Criteria->getGlue());
    }

    public function test_reset_glue()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $Criteria->resetGlue();

        $this->assertEquals(null, $Criteria->getGlue());
    }

}
