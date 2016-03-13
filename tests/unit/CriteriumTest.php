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

use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorker;
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\Utils\TestCase\MockeryTest;
use Everon\Component\Utils\Text\StartsWith;
use Everon\Component\CriteriaBuilder\Tests\Unit\Doubles\FactoryStub;

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
        $Container = new Container();
        $this->Factory = new FactoryStub($Container);
        $this->Factory->registerWorkerCallback('CriteriaBuilderFactoryWorker', function () {
            return $this->Factory->buildWorker(CriteriaBuilderFactoryWorker::class);
        });
        $this->CriteriaBuilderFactoryWorker = $this->Factory->getWorkerByName('CriteriaBuilderFactoryWorker');
    }

    public function test_Constructor()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface', $Criterium);
    }

    public function test_get_sql_part()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\SqlPartInterface', $Criterium->getSqlPart());
    }

    public function test_get_sql_part_with_sql()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $SqlPart = $Criterium->getSqlPart();

        $this->assertTrue(
            $this->textStartsWith($SqlPart->getSql(), 'foo = :foo_')
        );
    }

    public function test_is_null_when_equal_null()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', null);

        $SqlPart = $Criterium->getSqlPart();

        $this->assertEquals($SqlPart->getSql(), 'foo IS NULL');
    }

    public function test_is_not_null_when_not_equal_null()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '!=', null);

        $SqlPart = $Criterium->getSqlPart();

        $this->assertEquals($SqlPart->getSql(), 'foo IS NOT NULL');
    }

    public function test_get_sql_part_with_parameters()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $SqlPart = $Criterium->getSqlPart();

        $this->assertEquals('bar',
            current(array_keys(array_flip(
                $SqlPart->getParameters()
            )))
        );
    }

    public function test_to_Array()
    {
        $Criterium = $this->CriteriaBuilderFactoryWorker->buildCriteriaCriterium('foo', '=', 'bar');

        $data = $Criterium->toArray();

        $this->assertEquals([
            'column',
            'value',
            'operator_type',
            'placeholder',
            'glue',
            'SqlPart',
        ],
            array_keys($data)
        );
    }

}
