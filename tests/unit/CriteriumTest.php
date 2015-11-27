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

use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\Factory\Dependency\ContainerInterface;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\Utils\TestCase\MockeryTest;
use Everon\Component\Utils\Text\StartsWith;
use Mockery;
use Everon\Component\Factory\Tests\Unit\Doubles\FactoryStub;

class CriteriumTest extends MockeryTest
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
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', $Criterium);
    }

    public function test_is_factory_worker()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface', $Criterium->getCriteriaBuilderFactoryWorker());
    }

    public function test_get_sql_part()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\SqlPartInterface', $Criterium->getSqlPart());
    }

    public function test_get_sql_part_sql()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $SqlPart = $Criterium->getSqlPart();

        $this->assertTrue($this->textStartsWith($SqlPart->getSql(), 'foo = :foo_'));
    }

    public function test_get_sql_part_parameters()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $SqlPart = $Criterium->getSqlPart();

        $this->assertEquals('bar', current(array_keys(array_flip($SqlPart->getParameters()))));
    }
}
