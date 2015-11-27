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
use Everon\Component\CriteriaBuilder\OperatorInterface;
use Everon\Component\CriteriaBuilder\Tests\Unit\Doubles\FactoryStub;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\Factory\Dependency\ContainerInterface;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\Utils\TestCase\MockeryTest;
use Mockery;

class CriteriaBuilderTest extends MockeryTest
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

        /** @var ContainerInterface $Container */
        $this->Factory = new FactoryStub($Container);
        $this->CriteriaBuilderFactoryWorker = $this->Factory->getWorkerByName('CriteriaBuilder', 'Everon\Component\CriteriaBuilder');
    }

    public function testConstructor()
    {
        $Builder = new Builder();
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\BuilderInterface', $Builder);
    }

    public function test_where_or_and_should_build_criteria()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder->where('id', 'IN', [1,2,3])->orWhere('id', 'NOT IN', [4,5,6]);
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaBuilder->getCurrentContainer());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $CriteriaBuilder->getCurrentContainer()->getCriteria());
        $this->assertCount(2, $CriteriaBuilder->getCurrentContainer()->getCriteria()->toArray());

        $CriteriaBuilder->where('name', '!=', 'foo')->andWhere('name', '!=', 'bar');
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaBuilder->getCurrentContainer());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $CriteriaBuilder->getCurrentContainer()->getCriteria());
        $this->assertCount(2, $CriteriaBuilder->getCurrentContainer()->getCriteria()->toArray());
    }

    public function test_where_raw_should_use_raw_sql()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder->whereRaw('foo + bar')->andWhereRaw('1=1')->orWhereRaw('foo::bar()');
        $SqlPart = $CriteriaBuilder->toSqlPart();
        
        $this->assertEquals('WHERE (foo + bar AND 1=1 OR foo::bar())', $SqlPart->getSql());
        $this->assertEmpty($SqlPart->getParameters());
    }

    public function test_where_raw_with_parameters()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder->whereRaw(':foo + bar', ['foo' => 'foo_value'])->andWhereRaw('bar = :bar', ['bar' => 'bar_value'])->orWhereRaw('foo::bar()');
        $SqlPart = $CriteriaBuilder->toSqlPart();
        $parameters = $SqlPart->getParameters();
        
        $this->assertEquals('WHERE (:foo + bar AND bar = :bar OR foo::bar())', $SqlPart->getSql());
        $this->assertNotEmpty($parameters);
        $this->assertEquals($parameters['foo'], 'foo_value');
        $this->assertEquals($parameters['bar'], 'bar_value');
    }

    public function test_glue()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder->where('id', 'IN', [1,2,3])->orWhere('id', 'NOT IN', [4,5,6]);
        $CriteriaBuilder->glueByOr();
        $CriteriaBuilder->where('name', '!=', 'foo')->andWhere('name', '!=', 'bar');
        $CriteriaBuilder->glueByAnd();
        $CriteriaBuilder->where('bar', '=', 'foo')->andWhere('name', '=', 'Doe');

        $SqlPart = $CriteriaBuilder->toSqlPart();
        
        preg_match_all('@:([a-zA-Z]+)_(\d+)(_(\d+))?@', $SqlPart->getSql(), $sql_parameters);
        $sql_parameters = $sql_parameters[0];

        //strips : in front
        array_walk($sql_parameters, function(&$item){
            $item = substr($item, 1, strlen($item));
        });

        foreach ($sql_parameters as $key) {
            $this->assertTrue(array_key_exists($key, $SqlPart->getParameters()));
        }

        $this->assertEquals(count($SqlPart->getParameters()), count($sql_parameters));
        /*
         (id IN (:id_1263450107,:id_1088910886,:id_404821955) OR id NOT IN (:id_470739703,:id_562547487,:id_230395754)) OR
        (name != :name_1409254675 AND name != :name_190021050) AND
        (bar = :bar_1337676982 AND name = :name_391340793)"
            protected parameters -> array(10) [
                'id_470739703' => integer 4
                'id_562547487' => integer 5
                'id_230395754' => integer 6
                'id_1263450107' => integer 1
                'id_1088910886' => integer 2
                'id_404821955' => integer 3
                'name_190021050' => string (3) "bar"
                'name_1409254675' => string (3) "foo"
                'name_391340793' => string (3) "Doe"
                'bar_1337676982' => string (3) "foo"
            ]
         */
    }

    public function test_to_sql_part_should_return_valid_sql_part()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder->where('id', 'IN', [1,2,3])->orWhere('id', 'NOT IN', [4,5,6])->andWhere('name', '=', 'foo');
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaBuilder->getCurrentContainer());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $CriteriaBuilder->getCurrentContainer()->getCriteria());
        $this->assertCount(3, $CriteriaBuilder->getCurrentContainer()->getCriteria()->toArray());

        $CriteriaBuilder->where('modified', 'IS', null)->andWhere('name', '!=', null)->orWhere('id', '=', 55);
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaBuilder->getCurrentContainer());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $CriteriaBuilder->getCurrentContainer()->getCriteria());
        $this->assertCount(3, $CriteriaBuilder->getCurrentContainer()->getCriteria()->toArray());

        $SqlPart = $CriteriaBuilder->toSqlPart();
        
        preg_match_all('@:([a-zA-Z]+)_(\d+)(_(\d+))?@', $SqlPart->getSql(), $sql_parameters);
        $sql_parameters = $sql_parameters[0];
        
        //strips : in front
        array_walk($sql_parameters, function(&$item){
            $item = substr($item, 1, strlen($item));
        });
        
        foreach ($sql_parameters as $key) {
            $this->assertTrue(array_key_exists($key, $SqlPart->getParameters()));
        }

        $this->assertEquals(count($SqlPart->getParameters()), count($sql_parameters));
        /*
            sql: WHERE (id IN (:id_843451778,:id_897328169,:id_1377365551) OR id NOT IN (:id_1260952006,:id_519145813,:id_1367241593) AND name = :name_1178871152)
            OR (modified IS NULL AND name IS NOT NULL OR id = :id_895877163)
            parameters -> array(8) [
                'name_1178871152' => string (3) "foo"
                'id_1260952006' => integer 4
                'id_519145813' => integer 5
                'id_1367241593' => integer 6
                'id_843451778' => integer 1
                'id_897328169' => integer 2
                'id_1377365551' => integer 3
                'id_895877163' => integer 55
        */
    }

    public function test_to_string()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();
        $CriteriaBuilder->whereRaw('foo + bar')->andWhereRaw('1=1')->orWhereRaw('foo::bar()');
        $this->assertEquals('WHERE (foo + bar AND 1=1 OR foo::bar())', (string) $CriteriaBuilder);
    }

    public function test_to_array()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();
        $CriteriaBuilder->whereRaw('foo + bar')->andWhereRaw('1=1')->orWhereRaw('foo::bar()')->orWhere('id', '=', 55);
        $this->assertCount(1, $CriteriaBuilder->toArray());
    }

    public function test_limit_offset_group_by()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();
        $CriteriaBuilder->whereRaw('foo + bar')->andWhereRaw('1=1')->orWhereRaw('foo::bar()');
        $CriteriaBuilder->glueByAnd();
        $CriteriaBuilder->whereRaw('1=1');
        $CriteriaBuilder->setLimit(10);
        $CriteriaBuilder->setOffset(5);
        $CriteriaBuilder->setGroupBy('name,id');
        $CriteriaBuilder->setOrderBy(['name' => 'DESC', 'id' => 'ASC']);
        $SqlPart = $CriteriaBuilder->toSqlPart();
        
        $this->assertEquals('WHERE (foo + bar AND 1=1 OR foo::bar())
AND (1=1) GROUP BY name,id ORDER BY name DESC,id ASC LIMIT 10 OFFSET 5', $SqlPart->getSql());
    }

    public function test_merge_container_collection()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder->whereRaw('1=1');
        
        $ContainerCollection = $CriteriaBuilder->getContainerCollection(); //lets pretend it's a new thingy
        $CriteriaBuilder->appendContainerCollection($ContainerCollection);
        
        $this->assertCount(2, $CriteriaBuilder->getContainerCollection());
    }

    public function test_merge_register_operator()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        /** @var OperatorInterface $CustomOperator */
        Builder::registerOperator('CustomType', 'Everon\Component\CriteriaBuilder\Tests\Unit\Doubles\OperatorCustomTypeStub');

        $CriteriaBuilder->whereRaw('bar', null, 'CustomType');
        $CriteriaBuilder->andWhereRaw('foo', null, 'CustomType');

        $SqlPart = $CriteriaBuilder->toSqlPart();

        $this->assertEquals('WHERE (bar <sql for custom operator> NULL AND foo <sql for custom operator> NULL)', $SqlPart->getSql());
        $this->assertEquals([], $SqlPart->getParameters());
    }

}
