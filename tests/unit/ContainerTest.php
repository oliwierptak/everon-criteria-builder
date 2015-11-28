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
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\Factory\Dependency\ContainerInterface;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\Factory\Tests\Unit\Doubles\FactoryStub;
use Everon\Component\Utils\TestCase\MockeryTest;
use Everon\Component\Utils\Text\StartsWith;
use Mockery;

class ContainerTest extends MockeryTest
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

        /* @var ContainerInterface $Container */
        $this->Factory = new FactoryStub($Container);

        $this->CriteriaBuilderFactoryWorker = $this->Factory->getWorkerByName('CriteriaBuilder', 'Everon\Component\CriteriaBuilder');
    }

    public function test_Constructor()
    {
        $Criteria = Mockery::mock('Everon\Component\CriteriaBuilder\CriteriaInterface');

        /* @var CriteriaInterface  $Criteria */
        $Container = $this->CriteriaBuilderFactoryWorker->buildCriteriaContainer($Criteria, Builder::GLUE_AND);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $Container);
    }

    public function test_glue_by_and()
    {
        $Criteria = Mockery::mock('Everon\Component\CriteriaBuilder\CriteriaInterface');

        /* @var CriteriaInterface  $Criteria */
        $Container = $this->CriteriaBuilderFactoryWorker->buildCriteriaContainer($Criteria, Builder::GLUE_AND);

        $Container->resetGlue();
        $this->assertEquals(null, $Container->getGlue());

        $Container->glueByAnd();
        $this->assertEquals(Builder::GLUE_AND, $Container->getGlue());
    }

    public function test_glue_by_or()
    {
        $Criteria = Mockery::mock('Everon\Component\CriteriaBuilder\CriteriaInterface');

        /* @var CriteriaInterface  $Criteria */
        $Container = $this->CriteriaBuilderFactoryWorker->buildCriteriaContainer($Criteria, Builder::GLUE_AND);

        $Container->resetGlue();
        $this->assertEquals(null, $Container->getGlue());

        $Container->glueByOr();
        $this->assertEquals(Builder::GLUE_OR, $Container->getGlue());
    }

    public function test_reset_glue()
    {
        $Criteria = Mockery::mock('Everon\Component\CriteriaBuilder\CriteriaInterface');

        /* @var CriteriaInterface  $Criteria */
        $Container = $this->CriteriaBuilderFactoryWorker->buildCriteriaContainer($Criteria, Builder::GLUE_AND);

        $Container->resetGlue();

        $this->assertEquals(null, $Container->getGlue());
    }

}
