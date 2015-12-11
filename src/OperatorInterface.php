<?php
/**
 * This file is part of the Everon components.
 *
 * (c) Oliwier Ptak <everonphp@gmail.com>
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
    public function getType();

    /**
     * @return string
     */
    public function getTypeAsSql();

    /**
     * @param CriteriumInterface $Criterium
     *
     * @return array
     */
    public function toSqlPartData(CriteriumInterface $Criterium);

}
