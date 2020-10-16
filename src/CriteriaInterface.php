<?php declare(strict_types = 1);
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
use Everon\Component\Utils\Collection\ArrayableInterface;

interface CriteriaInterface extends ArrayableInterface
{
    public function where(CriteriumInterface $Criterium): CriteriaInterface;

    /**
     * @param CriteriumInterface $Criterium
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function andWhere(CriteriumInterface $Criterium): CriteriaInterface;

    /**
     * @param CriteriumInterface $Criterium
     *
     * @return \Everon\Component\CriteriaBuilder\CriteriaInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException
     */
    public function orWhere(CriteriumInterface $Criterium): CriteriaInterface;

    public function getCriteriumCollection(): CollectionInterface;

    public function setCriteriumCollection(CollectionInterface $CriteriumCollection);

    public function getGlue(): ?string;

    public function resetGlue(): void;

    public function glueByAnd(): void;

    public function glueByOr(): void;
}
