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
        $Container = Mockery::mock('Everon\Component\Factory\Dependency\ContainerInterface');
        $Container = new Container();

        /** @var ContainerInterface $Container */
        $this->Factory = new FactoryStub($Container);
        $this->CriteriaBuilderFactoryWorker = $this->Factory->getWorkerByName('CriteriaBuilder', 'Everon\Component\CriteriaBuilder');
    }

    public function test_Constructor()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();
        $Container = $this->CriteriaBuilderFactoryWorker->buildCriteriaContainer($Criteria, Builder::GLUE_AND);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $Container);
    }

    public function test_reset_glue()
    {
        $Criteria = $this->CriteriaBuilderFactoryWorker->buildCriteria();
        $Container = $this->CriteriaBuilderFactoryWorker->buildCriteriaContainer($Criteria, Builder::GLUE_AND);

        $Container->resetGlue();

        $this->assertEquals(null, $Container->getGlue());
    }
}
