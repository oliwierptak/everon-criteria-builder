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

use Everon\Component\Collection\CollectionInterface;
use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;
use Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException;
use Everon\Component\Utils\Collection\ArrayableInterface;

interface CriteriaInterface extends ArrayableInterface
{

    /**
     * @param CriteriumInterface $Criterium
     *
     * @return CriteriaInterface
     */
    public function where(CriteriumInterface $Criterium): CriteriaInterface;

    /**
     * @param CriteriumInterface $Criterium
     *
     * @throws NoSubQueryFoundException
     *
     * @return CriteriaInterface
     */
    public function andWhere(CriteriumInterface $Criterium): CriteriaInterface;

    /**
     * @param CriteriumInterface $Criterium
     *
     * @throws NoSubQueryFoundException
     *
     * @return CriteriaInterface
     */
    public function orWhere(CriteriumInterface $Criterium): CriteriaInterface;

    /**
     * @return CollectionInterface|CriteriumInterface[]
     */
    public function getCriteriumCollection(): CollectionInterface;

    /**
     * @param CollectionInterface $CriteriumCollection
     *
     * @return CriteriaInterface
     */
    public function setCriteriumCollection(CollectionInterface $CriteriumCollection): CriteriaInterface;

    /**
     * @return string|null
     */
    public function getGlue();

    /**
     * @return CriteriaInterface
     */
    public function resetGlue(): CriteriaInterface;

    /**
     * @return CriteriaInterface
     */
    public function glueByAnd(): CriteriaInterface;

    /**
     * @return CriteriaInterface
     */
    public function glueByOr(): CriteriaInterface;

}
