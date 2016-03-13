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
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorker;
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;
use Everon\Component\CriteriaBuilder\CriteriaInterface;
use Everon\Component\CriteriaBuilder\Operator\Equal;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\CriteriaBuilder\Tests\Unit\Doubles\FactoryStub;
use Everon\Component\Utils\TestCase\MockeryTest;
use Mockery;

class CriteriaBuilderFactoryWorkerTest extends MockeryTest
{

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

    /**
     * @return void
     */
    protected function useFaultyFactory()
    {
        $FactoryMock = Mockery::mock('Everon\Component\Factory\FactoryInterface');
        $FactoryMock->shouldReceive('injectDependencies')
            ->times(1)
            ->andThrow(new \Exception());

        $this->CriteriaBuilderFactoryWorker->setFactory($FactoryMock);
    }

    public function test_buildCriteria()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $Criteria);
    }

    /**
     * @expectedException \Everon\Component\Factory\Exception\UnableToInstantiateException
     * @expectedExceptionMessage Class "Everon\Component\CriteriaBuilder\Criteria" is not instantiable
     */
    public function test_buildCriteria_should_throw_exception()
    {
        $this->useFaultyFactory();

        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $Criteria);
    }

    public function test_buildCriteriaBuilder()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaBuilderInterface', $CriteriaBuilder);
    }

    /**
     * @expectedException \Everon\Component\Factory\Exception\UnableToInstantiateException
     * @expectedExceptionMessage Class "Everon\Component\CriteriaBuilder\CriteriaBuilder" is not instantiable
     */
    public function test_buildCriteriaBuilder_should_throw_exception()
    {
        $this->useFaultyFactory();

        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $CriteriaBuilder);
    }

    public function test_CriteriaBuilderHasWorker()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface', $CriteriaBuilder->getCriteriaBuilderFactoryWorker());
    }

    public function test_buildCriteriaCriterium()
    {
        $CriteriaCriterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('column', '=', 'foobar');

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', $CriteriaCriterium);
    }

    /**
     * @expectedException \Everon\Component\Factory\Exception\UnableToInstantiateException
     * @expectedExceptionMessage Class "Everon\Component\CriteriaBuilder\Criteria\Criterium" is not instantiable
     */
    public function test_buildCriteriaCriterium_should_throw_exception()
    {
        $this->useFaultyFactory();

        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('column', '=', 'foobar');

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriumInterface', $Criterium);
    }

    public function test_buildCriteriaContainer()
    {
        $Criteria = Mockery::mock('Everon\Component\CriteriaBuilder\CriteriaInterface');
        /* @var CriteriaInterface  $Criteria */
        $CriteriaContainer = $this->CriteriaBuilderFactoryWorker->buildCriteriaContainer(
            $Criteria, CriteriaBuilder::GLUE_AND
        );

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaContainer);
    }

    /**
     * @expectedException \Everon\Component\Factory\Exception\UnableToInstantiateException
     * @expectedExceptionMessage Class "Everon\Component\CriteriaBuilder\Criteria\Container" is not instantiable
     */
    public function test_buildCriteriaContainer_should_throw_exception()
    {
        $this->useFaultyFactory();

        $Criteria = Mockery::mock('Everon\Component\CriteriaBuilder\CriteriaInterface');
        /* @var CriteriaInterface  $Criteria */

        $CriteriaContainer = $this->CriteriaBuilderFactoryWorker->buildCriteriaContainer(
            $Criteria, CriteriaBuilder::GLUE_AND
        );

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\ContainerInterface', $CriteriaContainer);
    }

    public function test_buildCriteriaOperator()
    {
        $className = CriteriaBuilder::getOperatorClassNameBySqlOperator(Equal::TYPE_AS_SQL);
        $OperatorEqual = $this->CriteriaBuilderFactoryWorker->buildCriteriaOperator($className);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\OperatorInterface', $OperatorEqual);
    }

    public function test_buildSqlPart()
    {
        $SqlPart = $this->CriteriaBuilderFactoryWorker->buildSqlPart('foo = :foo_value', [
            'foo_value' => 'bar',
        ]);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\SqlPartInterface', $SqlPart);
    }

    /**
     * @expectedException \Everon\Component\Factory\Exception\UnableToInstantiateException
     * @expectedExceptionMessage Class "Everon\Component\CriteriaBuilder\SqlPart" is not instantiable
     */
    public function test_buildSqlPart_should_throw_exception()
    {
        $this->useFaultyFactory();

        $SqlPart = $this->CriteriaBuilderFactoryWorker->buildSqlPart('foo = :foo_value', [
            'foo_value' => 'bar',
        ]);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\SqlPartInterface', $SqlPart);
    }

}
