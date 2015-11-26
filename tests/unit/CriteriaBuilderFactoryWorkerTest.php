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
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;
use Everon\Component\CriteriaBuilder\CriteriaInterface;
use Everon\Component\CriteriaBuilder\Operator\Equal;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\Factory\Dependency\ContainerInterface;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\Factory\Tests\Unit\Doubles\FactoryStub;
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

    public function test_CriteriaBuilderFactoryWorker_is_a_worker()
    {
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface', $this->CriteriaBuilderFactoryWorker);
    }

    public function test_buildCriteria()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $Criteria);
    }

    public function test_buildCriteriaBuilder()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\BuilderInterface', $CriteriaBuilder);
    }

    public function test_CriteriaBuilderHasWorker()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface', $CriteriaBuilder->getCriteriaBuilderFactoryWorker());
    }

    public function test_buildCriteriaCriterium()
    {
        $CriteriaCriterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium(
            'column',
            '=',
            'foobar'
        );

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', $CriteriaCriterium);
    }

    public function test_buildCriteriaContainer()
    {
        $Criteria = Mockery::mock('Everon\Component\CriteriaBuilder\CriteriaInterface');
        /** @var CriteriaInterface  $Criteria */
        $CriteriaContainer = $this->CriteriaBuilderFactoryWorker->buildCriteriaContainer(
            $Criteria, Builder::GLUE_AND
        );

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaContainer);
    }

    public function test_buildCriteriaOperator()
    {
        $OperatorEqual = $this->CriteriaBuilderFactoryWorker->buildCriteriaOperator(Equal::TYPE_NAME);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\OperatorInterface', $OperatorEqual);
    }

    public function test_buildSqlPart()
    {
        $SqlPart = $this->CriteriaBuilderFactoryWorker->buildSqlPart('foo = :foo_value', [
            'foo_value' => 'bar'
        ]);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\SqlPartInterface', $SqlPart);
    }

}
