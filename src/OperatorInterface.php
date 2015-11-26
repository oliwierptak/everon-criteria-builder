<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <EveronFramework@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder;

use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;

interface OperatorInterface
{
    /**
     * @return string
     */
    function getType();

    /**
     * @return string
     */
    function getTypeAsSql();

    /**
     * @param CriteriumInterface $Criterium
     * @return array
     */
    function toSqlPartData(CriteriumInterface $Criterium);
}
