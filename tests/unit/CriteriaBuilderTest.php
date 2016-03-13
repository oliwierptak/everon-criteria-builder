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
use Everon\Component\CriteriaBuilder\OperatorInterface;
use Everon\Component\CriteriaBuilder\Tests\Unit\Doubles\FactoryStub;
use Everon\Component\CriteriaBuilder\Tests\Unit\Doubles\OperatorCustomTypeStub;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\Factory\FactoryInterface;
use Everon\Component\Utils\TestCase\MockeryTest;

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
        $this->Factory = new FactoryStub($Container);
        $this->Factory->registerWorkerCallback('CriteriaBuilderFactoryWorker', function () {
            return $this->Factory->buildWorker(CriteriaBuilderFactoryWorker::class);
        });
        $this->CriteriaBuilderFactoryWorker = $this->Factory->getWorkerByName('CriteriaBuilderFactoryWorker');
    }

    public function test_constructor()
    {
        $Builder = new CriteriaBuilder();
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaBuilderInterface', $Builder);
    }

    public function test_where_or_and_should_build_criteria_and_advance_currentContainerIndex()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder
            ->where('id', 'IN', [1, 2, 3])
            ->orWhere('id', 'NOT IN', [4, 5, 6]);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaBuilder->getCurrentContainer());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $CriteriaBuilder->getCurrentContainer()->getCriteria());
        $this->assertCount(2, $CriteriaBuilder->getCurrentContainer()->getCriteria()->toArray());

        $CriteriaBuilder
            ->where('name', '!=', 'foo');

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaBuilder->getCurrentContainer());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $CriteriaBuilder->getCurrentContainer()->getCriteria());
        $this->assertCount(1, $CriteriaBuilder->getCurrentContainer()->getCriteria()->toArray());
    }

    public function test_where_raw_should_use_raw_sql()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder
            ->whereRaw('foo + bar')
            ->andWhereRaw('1=1')
            ->orWhereRaw('foo::bar()');

        $SqlPart = $CriteriaBuilder->toSqlPart();

        $this->assertEquals('WHERE (foo + bar AND 1=1 OR foo::bar())', $SqlPart->getSql());
        $this->assertEmpty($SqlPart->getParameters());
    }

    public function test_where_raw_with_parameters()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder
            ->whereRaw(':foo + bar', ['foo' => 'foo_value'])
            ->andWhereRaw('bar = :bar', ['bar' => 'bar_value'])
            ->orWhereRaw('foo::bar()');

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

        $CriteriaBuilder
                ->where('id', 'IN', [1, 2, 3])
                ->orWhere('id', 'NOT IN', [4, 5, 6])
            ->glueByOr()
                ->where('name', '!=', 'foo')
                ->andWhere('name', '!=', 'bar')
            ->glueByAnd()
                ->where('bar', '=', 'foo')
                ->andWhere('name', '=', 'Doe')
            ->glueByOr()
                ->where('score', 'BETWEEN', [6, 12]);

        $SqlPart = $CriteriaBuilder->toSqlPart();

        preg_match_all('@:([a-zA-Z]+)_(\d+)(_(\d+))?@', $SqlPart->getSql(), $sql_parameters);
        $sql_parameters = $sql_parameters[0];

        //strips : in front
        array_walk($sql_parameters, function (&$item) {
            $item = substr($item, 1, strlen($item));
        });

        foreach ($sql_parameters as $key) {
            $this->assertTrue(array_key_exists($key, $SqlPart->getParameters()));
        }

        $this->assertEquals(count($SqlPart->getParameters()), count($sql_parameters));
        /*
        WHERE (id IN (:id_1160359716_621731305,:id_1160359716_1337919923,:id_1160359716_1442916869) OR id NOT IN (:id_1079369094_998600711,:id_1079369094_523094061,:id_1079369094_856216937))
        OR (name != :name_454657188 AND name != :name_195323804)
        AND (bar = :bar_65757800 AND name = :name_1296684151)
        OR (score BETWEEN :score_341517479_392318756 AND :score_341517479_1370669169)
        "
            parameters -> array(12) [
              "id_773052891_1328551757" => 4
              "id_773052891_453206087" => 5
              "id_773052891_761603928" => 6
              "id_448219632_551348544" => 1
              "id_448219632_193500176" => 2
              "id_448219632_89837912" => 3
              "name_941076102" => "bar"
              "name_810836106" => "foo"
              "name_484902645" => "Doe"
              "bar_68579147" => "foo"
              "score_251787527_1361091899" => 6
              "score_251787527_80095256" => 12
            ]
         */
    }

    public function test_to_sql_part_should_return_valid_sql_part()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder
            ->where('id', 'IN', [1, 2, 3])
            ->orWhere('id', 'NOT IN', [4, 5, 6])
            ->andWhere('name', '=', 'foo');

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaBuilder->getCurrentContainer());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $CriteriaBuilder->getCurrentContainer()->getCriteria());
        $this->assertCount(3, $CriteriaBuilder->getCurrentContainer()->getCriteria()->toArray());

        $CriteriaBuilder
            ->where('modified', 'IS', null)
            ->andWhere('name', '!=', null)
            ->orWhere('id', '=', 55);

        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\Criteria\ContainerInterface', $CriteriaBuilder->getCurrentContainer());
        $this->assertInstanceOf('Everon\Component\CriteriaBuilder\CriteriaInterface', $CriteriaBuilder->getCurrentContainer()->getCriteria());
        $this->assertCount(3, $CriteriaBuilder->getCurrentContainer()->getCriteria()->toArray());

        $SqlPart = $CriteriaBuilder->toSqlPart();

        preg_match_all('@:([a-zA-Z]+)_(\d+)(_(\d+))?@', $SqlPart->getSql(), $sql_parameters);
        $sql_parameters = $sql_parameters[0];

        //strips : in front
        array_walk($sql_parameters, function (&$item) {
            $item = substr($item, 1, strlen($item));
        });

        foreach ($sql_parameters as $key) {
            $this->assertTrue(array_key_exists($key, $SqlPart->getParameters()));
        }

        $this->assertEquals(count($SqlPart->getParameters()), count($sql_parameters));
        /*
            sql: WHERE (id IN (:id_843451778,:id_897328169,:id_1377365551) OR id NOT IN (:id_1260952006,:id_519145813,:id_1367241593) AND name = :name_1178871152)
            AND (modified IS NULL AND name IS NOT NULL OR id = :id_895877163)
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

        $CriteriaBuilder
            ->whereRaw('foo + bar')
            ->andWhereRaw('1=1')
            ->orWhereRaw('foo::bar()');

        $this->assertEquals('WHERE (foo + bar AND 1=1 OR foo::bar())', (string) $CriteriaBuilder);
    }

    public function test_to_array()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder
            ->whereRaw('foo + bar')
            ->andWhereRaw('1=1')
            ->orWhereRaw('foo::bar()')
            ->orWhere('id', '=', 55);

        $this->assertCount(1, $CriteriaBuilder->toArray());
    }

    public function test_limit_offset_group_by()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();
        $CriteriaBuilder
            ->whereRaw('foo + bar')
                ->andWhereRaw('1=1')
                ->orWhereRaw('foo::bar()')
            ->glueByAnd()
                ->whereRaw('1=1')
            ->setLimit(10)
            ->setOffset(5)
            ->setGroupBy('name,id')
            ->setOrderBy(['name' => 'DESC', 'id' => 'ASC']);

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

    public function test_register_custom_operator()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        /* @var OperatorInterface $CustomOperator */
        CriteriaBuilder::registerOperator(
            OperatorCustomTypeStub::TYPE_AS_SQL,
            OperatorCustomTypeStub::class
        );

        $CriteriaBuilder
            ->whereRaw('bar', null, OperatorCustomTypeStub::TYPE_AS_SQL)
            ->andWhereRaw('foo', null, OperatorCustomTypeStub::TYPE_AS_SQL);

        $SqlPart = $CriteriaBuilder->toSqlPart();

        $this->assertEquals('WHERE (bar <sql for custom operator> NULL AND foo <sql for custom operator> NULL)', $SqlPart->getSql());
        $this->assertEquals([], $SqlPart->getParameters());
    }

    public function test_sql_should_be_formatted_according_to_sqlTemplate()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder
            ->sql('SELECT * FROM user u LEFT JOIN user_session us ON u.id = us.user_id AND (%s)')
                ->whereRaw("created_at >= NOW() - '24 hours'")
                ->andWhereRaw('session_id IS NOT NULL')
            ->glueByOr()
                ->whereRaw('session_id IS NULL');

        $SqlPart = $CriteriaBuilder
            ->toSqlPart();

        $this->assertEquals("SELECT * FROM user u LEFT JOIN user_session us ON u.id = us.user_id AND ((created_at >= NOW() - '24 hours' AND session_id IS NOT NULL)
OR (session_id IS NULL))", $SqlPart->getSql());
        $this->assertEquals([], $SqlPart->getParameters());
    }

    public function test_sqlTemplate_default()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $this->assertEquals('WHERE %s', $CriteriaBuilder->getSqlTemplate());
    }

    public function test_extraParameters_with_custom_sql()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder
            ->sql('SELECT * FROM user u LEFT JOIN user_session us ON u.id = :user_id AND %s ')
            ->whereRaw('1=1')
            ->setParameter('user_id', 123);

        $SqlPart = $CriteriaBuilder
            ->toSqlPart();

        $this->assertEquals('SELECT * FROM user u LEFT JOIN user_session us ON u.id = :user_id AND (1=1)', $SqlPart->getSql());

        $this->assertEquals([
            'user_id' => 123,
        ], $SqlPart->getParameters());
    }

    public function test_extraParameterCollection_ensures_names_do_not_contain_dots()
    {
        $CriteriaBuilder = $this->CriteriaBuilderFactoryWorker->buildCriteriaBuilder();

        $CriteriaBuilder
            ->setParameterCollection([
                'some.name' => 'foo.bar',
                'user.id' => 123,
            ]);

        $SqlPart = $CriteriaBuilder
            ->toSqlPart();

        $this->assertEquals([
            'some_name' => 'foo.bar',
            'user_id' => 123,
        ], $SqlPart->getParameters());
    }

}
