<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <EveronFramework@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Criteria;

use Everon\Component\CriteriaBuilder\CriteriaInterface;

interface ContainerInterface
{
    /**
     * @return CriteriaInterface
     */
    function getCriteria();

    /**
     * @param CriteriaInterface $Criteria
     */
    function setCriteria(CriteriaInterface $Criteria);

    /**
     * @return string
     */
    function getGlue();

    /**
     * @param string $glue
     */
    function setGlue($glue);

    function resetGlue();

    function glueByAnd();

    function glueByOr();
}
