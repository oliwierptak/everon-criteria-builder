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
use Everon\Component\Factory\Dependency\ContainerInterface;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\CriteriaBuilder\Tests\Unit\Doubles\FactoryStub;
use Everon\Component\Utils\TestCase\MockeryTest;

class SqlPartTest extends MockeryTest
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

        /* @var ContainerInterface $Container */
        $this->Factory = new FactoryStub($Container);
        $this->CriteriaBuilderFactoryWorker = $this->Factory->buildWorker(CriteriaBuilderFactoryWorker::class);
    }

    public function test_Constructor()
    {
        $SqlPart = $this->CriteriaBuilderFactoryWorker->buildSqlPart('foo = :foo_value', [
            'foo_value' => 'bar',
        ]);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\SqlPartInterface', $SqlPart);
    }

    public function test_GetSql_should_return_sql_query_string()
    {
        $SqlPart = $this->CriteriaBuilderFactoryWorker->buildSqlPart('foo = :foo_value', [
            'foo_value' => 'bar',
        ]);

        $this->assertEquals('foo = :foo_value', $SqlPart->getSql());
    }

    public function test_GetParameters_should_return_associative_array_with_parameters()
    {
        $SqlPart = $this->CriteriaBuilderFactoryWorker->buildSqlPart('foo = :foo_value', [
            'foo_value' => 'bar',
        ]);

        $this->assertEquals(['foo_value' => 'bar'], $SqlPart->getParameters());
    }

    public function test_GetParameterByValue_should_return_parameter_value()
    {
        $SqlPart = $this->CriteriaBuilderFactoryWorker->buildSqlPart('foo = :foo_value', [
            'foo_value' => 'bar',
        ]);

        $this->assertEquals('bar', $SqlPart->getParameterValue('foo_value'));
    }

    public function test_toArray()
    {
        $SqlPart = $this->CriteriaBuilderFactoryWorker->buildSqlPart('foo = :foo_value', [
            'foo_value' => 'bar',
        ]);

        $this->assertEquals([
            'sql' => 'foo = :foo_value',
            'parameters' => [
                'foo_value' => 'bar',
            ],
        ], $SqlPart->toArray());
    }

}
