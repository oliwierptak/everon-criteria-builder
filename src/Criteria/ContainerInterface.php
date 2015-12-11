<?php
/**
 * This file is part of the Everon components.
 *
 * (c) Oliwier Ptak <everonphp@gmail.com>
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
    public function getCriteria();

    /**
     * @param CriteriaInterface $Criteria
     */
    public function setCriteria(CriteriaInterface $Criteria);

    /**
     * @return string
     */
    public function getGlue();

    /**
     * @param string $glue
     */
    public function setGlue($glue);

    /**
     * @return void
     */
    public function resetGlue();

    /**
     * @return void
     */
    public function glueByAnd();

    /**
     * @return void
     */
    public function glueByOr();

}
