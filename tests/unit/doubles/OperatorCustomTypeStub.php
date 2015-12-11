<?php
/**
 * This file is part of the Everon components.
 *
 * (c) Oliwier Ptak <everonphp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Tests\Unit\Doubles;

use Everon\Component\CriteriaBuilder\AbstractOperator;

class OperatorCustomTypeStub extends AbstractOperator
{

    const TYPE_NAME = 'CustomType';
    const TYPE_AS_SQL = '<sql for custom operator>';

}
