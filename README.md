# everon-criteria-builder
Beta version

# Everon Criteria Builder v0.5
Library to generate complete ```SQL WHERE``` statements, with simple, fluid and intuitive interface.

## Requirements
* Php 5.5+

## Features
* Fluid interface
* Minimized file/memory access/usage due to callbacks and lazy load
* Intuitive Interface: clear, small and simple API
* Convention over configuration
* Clean code

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
```

Will output:
```sql
WHERE (bar <sql for custom operator> NULL AND foo <sql for custom operator> :foo_1337676981)
```

See https://github.com/oliwierptak/everon-criteria-builder/tree/development/src/Operator for more examples


### Tests Driven
[Check the tests for more examples of usage here](https://github.com/oliwierptak/everon-criteria-builder/tree/development/tests/unit)
