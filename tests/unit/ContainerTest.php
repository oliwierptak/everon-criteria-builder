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
use Everon\Component\CriteriaBuilder\Criteria\ContainerInterface as CriteriaContainerInterface;
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorker;
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;
use Everon\Component\CriteriaBuilder\CriteriaInterface;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\CriteriaBuilder\Tests\Unit\Doubles\FactoryStub;
use Everon\Component\Utils\TestCase\MockeryTest;
use Everon\Component\Utils\Text\StartsWith;
use Mockery;

class ContainerTest extends MockeryTest
{

    use StartsWith;

    /**
     * @var CriteriaContainerInterface
     */
    protected $CriteriaContainer;

    protected function setUp()
    {
        $Container = new Container();
        $Factory = new FactoryStub($Container);
        /* @var CriteriaBuilderFactoryWorkerInterface  $CriteriaBuilderFactoryWorker */
        $CriteriaBuilderFactoryWorker = $Factory->buildWorker(CriteriaBuilderFactoryWorker::class);

        $Criteria = Mockery::mock('Everon\Component\CriteriaBuilder\CriteriaInterface');
        /* @var CriteriaInterface  $Criteria */
        $this->CriteriaContainer = $CriteriaBuilderFactoryWorker->buildCriteriaContainer($Criteria, CriteriaBuilder::GLUE_AND);
    }

    public function test_Constructor()
    {
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $this->CriteriaContainer);
    }

    public function test_glue_by_and()
    {
        $this->CriteriaContainer->resetGlue();
        $this->assertEquals(null, $this->CriteriaContainer->getGlue());

        $this->CriteriaContainer->glueByAnd();
        $this->assertEquals(CriteriaBuilder::GLUE_AND, $this->CriteriaContainer->getGlue());
    }

    public function test_glue_by_or()
    {
        $this->CriteriaContainer->resetGlue();
        $this->assertEquals(null, $this->CriteriaContainer->getGlue());

        $this->CriteriaContainer->glueByOr();
        $this->assertEquals(CriteriaBuilder::GLUE_OR, $this->CriteriaContainer->getGlue());
    }

    public function test_reset_glue()
    {
        $this->CriteriaContainer->resetGlue();

        $this->assertEquals(null, $this->CriteriaContainer->getGlue());
    }

}
