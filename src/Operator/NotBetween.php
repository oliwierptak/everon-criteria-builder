<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <EveronFramework@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Operator;

class NotBetween extends Between
{
    const TYPE_NAME = 'NotBetween';
    const TYPE_AS_SQL = 'NOT BETWEEN';
}
