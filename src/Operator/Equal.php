<?php
/**
 * This file is part of the Everon components.
 *
 * (c) Oliwier Ptak <everonphp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Operator;

use Everon\Component\CriteriaBuilder\AbstractOperator;
use Everon\Component\CriteriaBuilder\OperatorInterface;

class Equal extends AbstractOperator implements OperatorInterface
{

    const TYPE_NAME = 'Equal';
    const TYPE_AS_SQL = '=';

}
