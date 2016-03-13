# Everon Criteria Builder
Library to generate complete ```SQL WHERE``` statements, with simple, fluid and intuitive interface.

## Works with
* Php 5.6+
* Php 7
* Hhvm

## Features
* It's not a DQL
* SQL for what's important, fluid interface for boring stuff
* Automatic PDO parameter name escaping and uniqueness, custom parameters
* Fluid interface
* Easily to create multiple conditions
* Almost 20 ready to use Operators
* Easy to extend with Custom Operators
* Intuitive Interface: clear, small and simple API
* Clean code

## No boring Sql
### Focus on what's important
You can attach your own ```SQL``` via ```CriteriaBuilder->sql($sql)``` and have easy and flexible way of generating fast *sql
queries* without dealing with boring string concatenations, code duplication and vast amount of if/else, switch statements or constants
required to handle logic related to *LIMIT*, *OFFSET* or *SORT* statements.
All those boring parts were eliminated with ```CriteriaBuilderInterface```.

### Hammer won't do when you need a screwdriver
Putting boring stuff aside you have full control on how the ```SQL``` is constructed, which is helpful for highly complex,
complicated or very specific queries where using *DQL* makes things actually harder then easier.
*DQL* is great for everyday use, however sometimes you need to express yourself in very specific way,
and *raw* ```SQL``` is the best way to get you there.

### Translate request into something database can understand
Easy to translate request parameters into something database can understand with
```Operators```, ```where``` statements and methods like ```setLimit```, ```setOffset```, or ```setOrderBy```.
Useful for pagination or filtering, for example.

Clear separation between ```SQL```, ```SQL PARAMETERS```, and applying concepts like *aggregation*, *sort*, or *limit*.
Now you can focus only on what's important, the ```SQL``` part.

Very easy to use with ```PDO``` thanks to ```SqlPartInterface```
```php
$sth = $dbh->prepare($SqlPart->getSql());
$sth->execute($SqlPart->getParameters());
```


## Examples
### Simple Query
```php
$CriteriaBuilder
    ->where('id', '=', 123)
```

Will be converted into:
```sql
WHERE (id = :id_843451772)
```

With parameters:
```txt
array(8) [
  'id_843451772' => integer 123
]
```

### Where, orWhere, andWhere
Each ```where``` statement creates new Container with Criteria object.
A Criteria object contains set of Criterium objects.
A Criterium is a condition.

You can append Criterium by using ```andWhere``` and ```orWhere``` methods.

Every time you use ```where``` statement a new Criteria will be created, ready for new set of conditions.

```php
$CriteriaBuilder
    ->where('id', 'IN', [1,2,3])
        ->orWhere('id', 'NOT IN', [4,5,6])
        ->andWhere('name', '=', 'foo');
    ->where('modified', 'IS', null)
        ->andWhere('name', '!=', null)
        ->orWhere('id', '=', 55);
```

Will be converted into:
```sql
WHERE (
    id IN (:id_843451778,:id_897328169,:id_1377365551)
    OR id NOT IN (:id_1260952006,:id_519145813,:id_1367241593)
    AND name = :name_1178871152
)
AND (
    modified IS NULL
    AND name IS NOT NULL
    OR id = :id_895877163
)
```

With parameters:
```txt
array(8) [
  'name_1178871152' => string (3) "foo"
  'id_1260952006' => integer 4
  'id_519145813' => integer 5
  'id_1367241593' => integer 6
  'id_843451778' => integer 1
  'id_897328169' => integer 2
  'id_1377365551' => integer 3
  'id_895877163' => integer 55
```

To connect Criteria with ```OR``` operator use ```glueByOr``` method.
```php
$CriteriaBuilder
    ->where('id', 'IN', [1,2,3])
        ->orWhere('id', 'NOT IN', [4,5,6])
        ->andWhere('name', '=', 'foo');
    ->glueByOr()
    ->where('modified', 'IS', null)
        ->andWhere('name', '!=', null)
        ->orWhere('id', '=', 55);
```

Will be converted into:
```sql
WHERE (
    id IN (:id_843451778,:id_897328169,:id_1377365551)
    OR id NOT IN (:id_1260952006,:id_519145813,:id_1367241593)
    AND name = :name_1178871152
)
OR (
    modified IS NULL
    AND name IS NOT NULL
    OR id = :id_895877163
)
```


### RAW SQL
RAW SQL is easy to implement with ```whereRaw``` methods.
```php
$CriteriaBuilder
    ->whereRaw('foo + bar')
    ->andWhereRaw('1=1')
    ->orWhereRaw('foo::bar()');
```

Will be converted into:
```sql
WHERE (foo + bar AND 1=1 OR foo::bar())
```


### Group By
Group By is easily usable with ```setGroupBy``` method
```php
$CriteriaBuilder
    ->where('name', '!=', 'foo')
        ->andWhere('id', '=', 123)
    ->setGroupBy('name,id');
```

Will be converted into:
```sql
WHERE (name != :name_1178871154 AND id = :id_897328160)
GROUP BY name,id
```

With parameters:
```txt
array(8) [
  'name_1178871154' => string (3) "foo"
  'id_897328160' => integer 123
]
```

### Limit and Offset
Pretty straightforward with ```setLimit``` and ```setOffset``` methods.
```php
$CriteriaBuilder
    ->whereRaw('foo + bar')
        ->andWhereRaw('1=1')
        ->orWhereRaw('foo::bar()');
    ->setLimit(10)
    ->setOffset(5);
```

Will be converted into:
```sql
WHERE (foo + bar AND 1=1 OR foo::bar())
LIMIT 10 OFFSET 5
```

### Order By
Order By is implemented using ```ASC``` and ```DESC``` keywords, in an associative array with ```setOrderBy``` method.
```php
$CriteriaBuilder
    ->whereRaw('foo + bar')
        ->andWhereRaw('1=1')
        ->orWhereRaw('foo::bar()')
    ->setOrderBy([
        'name' => 'DESC',
        'id' => 'ASC'
    ]);
```

Will be converted into:
```sql
WHERE (foo + bar AND 1=1 OR foo::bar())
ORDER BY name DESC,id ASC
```


### Custom Gluing
Manual Criteria handling is also possible by using the ```glue``` methods.

```php
$CriteriaBuilder
        ->where('id', 'IN', [1,2,3])
        ->orWhere('id', 'NOT IN', [4,5,6])
    ->glueByOr()
        ->where('name', '!=', 'foo')
        ->andWhere('email', '!=', 'foo@bar')
    ->glueByAnd()
        ->where('bar', '=', 'bar')
        ->andWhere('name', '=', 'Doe');

$CriteriaBuilder->setLimit(10);
$CriteriaBuilder->setOffset(5);
$CriteriaBuilder->setGroupBy('name,id');
$CriteriaBuilder->setOrderBy(['name' => 'DESC', 'id' => 'ASC']);
```

Will be converted into:
```sql
(id IN (:id_1263450107,:id_1088910886,:id_404821955) OR id NOT IN (:id_470739703,:id_562547487,:id_230395754))
OR (name != :name_1409254675 AND email != :name_190021050)
AND (bar = :bar_1337676982 AND name = :name_391340793)
GROUP BY name,id
ORDER BY name DESC,id ASC
LIMIT 10 OFFSET 5
```

With parameters:
```txt
array(10) [
    'id_470739703' => integer 4
    'id_562547487' => integer 5
    'id_230395754' => integer 6
    'id_1263450107' => integer 1
    'id_1088910886' => integer 2
    'id_404821955' => integer 3
    'name_190021050' => string (3) "foo@bar"
    'name_1409254675' => string (3) "foo"
    'name_391340793' => string (3) "Doe"
    'bar_1337676982' => string (3) "bar"
]
```

### Operators
There are almost 20 operators ready for use like Equal, NotIn, Between or Is.
[Check them all here](https://github.com/oliwierptak/everon-criteria-builder/tree/development/src/Operator).

#### Equal
```php
$CriteriaBuilder->where('foo', '=', 'bar');
```

Will output:
```sql
WHERE (foo = :foo_1337676681)
```

#### NotIn
```php
$CriteriaBuilder->where('foo', 'NOT IN', ['bar', 'buzz']);
```

Will output:
```sql
WHERE (foo NOT IN [:foo_1337676681, :foo_1337776681)
```

#### Between
There must be exactly 2 parameters provided or an exception will be thrown.

```php
$CriteriaBuilder->where('foo', 'BETWEEN', ['bar', 'buzz']);
```

Will output:
```sql
WHERE (foo BETWEEN :foo_1337676681 AND :foo_1337776681)
```

There are many more. [See here for more examples](https://github.com/oliwierptak/everon-criteria-builder/tree/development/src/Operator).

### Custom Operators
You can register your own Operators with:
```php
/**
 * @param $sql_type
 * @param $operator_class_name
 *
 * @return void
 */
public static function registerOperator($sql_type, $operator_class_name);
```

For example:
```php
class OperatorCustomTypeStub extends AbstractOperator
{
    const TYPE_NAME = 'CustomType';
    const TYPE_AS_SQL = '<sql for custom operator>';
}

Builder::registerOperator(OperatorCustomTypeStub::TYPE_AS_SQL, 'Some\Namespace\OperatorCustomTypeStub');
```


You can use your own operator with ```raw``` methods.
```php
$CriteriaBuilder->whereRaw('bar', null, OperatorCustomTypeStub::TYPE_AS_SQL);
$CriteriaBuilder->andWhereRaw('foo', ['foo' => 'bar'], OperatorCustomTypeStub::TYPE_AS_SQL);
$CriteriaBuilder->orWhereRaw('foo', 'bar', OperatorCustomTypeStub::TYPE_AS_SQL);
```

Will output:
```sql
WHERE (bar <sql for custom operator> NULL AND foo <sql for custom operator> :foo_1337676981
    OR foo <sql for custom operator> :foo_2137676760 )
```

See https://github.com/oliwierptak/everon-criteria-builder/tree/development/src/Operator for more examples

### How to use
Dependency Injection is done with [Everon Factory](https://github.com/oliwierptak/everon-factory).

Initialize with ```CriteriaBuilderFactoryWorker->buildCriteriaBuilder()```.
```php
use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;
use Everon\Component\Factory\Dependency\Container;
use Everon\Component\Factory\Factory;

include('vendor/autoload.php');

$Container = new Container();
$Factory = new Factory($Container);
$Factory->registerWorkerCallback('CriteriaBuilderFactoryWorker', function() use ($Factory) {
    return $Factory->buildWorker(CriteriaBuilderFactoryWorker::class);
});

$CriteriaBuilderFactoryWorker = $Factory->getWorkerByName('CriteriaBuilderFactoryWorker');
$CriteriaBuilder = $CriteriaBuilderFactoryWorker->buildCriteriaBuilder();
```

Setup your conditions.
```php
$CriteriaBuilder
        ->where('sku', 'LIKE', '13%')
        ->orWhere('id', 'IN', [1, 2, 3])
    ->glueByOr()
        ->where('created_at', '>', '2015-12-03 12:27:22');
```

Append criteria string to already existing sql.

```php
$sql = 'SELECT * FROM <TABLE>';
$sql = $sql . (string) $CriteriaBuilder;
$sth = $dbh->prepare($sql);
```

Fetch sample data.
After you attached ```SQL``` to the ```CriteriaBuilder```, it's even easier to retrieve *sql query* and its *parameters*,
with ```SqlPartInterface``` and methods like ```getSql``` and ```getParameters```.

```php
$dbh = new \PDO('mysql:host=127.0.0.1;dbname=DATABASE', 'root', '');
$SqlPart = $CriteriaBuilder->toSqlPart();
$sth = $dbh->prepare($SqlPart->getSql());
$sth->execute($SqlPart->getParameters());
```

### Putting it all together
```php
$dbh = new \PDO('mysql:host=127.0.0.1;dbname=DATABASE', 'root', '');
$CriteriaBuilder
    ->sql('SELECT * FROM fooTable f LEFT JOIN barTable b ON f.bar_id = b.id AND f.is_active = :is_active')
    ->where('bar', '=', 1)
        ->andWhere('foo', 'NOT IN', [1,2,3])
        ->orWhereRaw('foo::bar() IS NULL')
    ->setParameter('is_active', false)
    ->setLimit(10)
    ->setOffset(20)
    ->setOrderBy(['foo' => 'DESC']);

$SqlPart = $CriteriaBuilder->toSqlPart();
$sth = $dbh->prepare($SqlPart->getSql());
$sth->execute($SqlPart->getParameters());
$data = $sth->fetchAll(PDO::FETCH_ASSOC);
```

### Test Driven
[Check the tests for more examples of usage here](https://github.com/oliwierptak/everon-criteria-builder/tree/development/tests/unit)
