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

use Everon\Component\Collection\Collection;
use Everon\Component\Collection\CollectionInterface;
use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;
use Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException;
use Everon\Component\Utils\Collection\ToArray;

class Criteria implements CriteriaInterface
{
    use ToArray;

    /**
     * @var \Everon\Component\Collection\CollectionInterface
     */
    protected $CriteriumCollection;

    /**
     * @var string
     */
    protected $glue = CriteriaBuilder::GLUE_AND;

    protected function getArrayableData($deep = false): array
    {
        return $this->getCriteriumCollection()->toArray($deep);
    }

    public function where(CriteriumInterface $Criterium): CriteriaInterface
    {
        $Criterium->resetGlue();
        $this->getCriteriumCollection()->append($Criterium);

        return $this;
    }

    public function andWhere(CriteriumInterface $Criterium): CriteriaInterface
    {
        if ($this->getCriteriumCollection()->isEmpty()) {
            throw new NoSubQueryFoundException();
        }

        $Criterium->glueByAnd();
        $this->getCriteriumCollection()->append($Criterium);

        return $this;
    }

    public function orWhere(CriteriumInterface $Criterium): CriteriaInterface
    {
        if ($this->getCriteriumCollection()->isEmpty()) {
            throw new NoSubQueryFoundException();
        }

        $Criterium->glueByOr();
        $this->getCriteriumCollection()->append($Criterium);

        return $this;
    }

    public function getCriteriumCollection(): CollectionInterface
    {
        if ($this->CriteriumCollection === null) {
            $this->CriteriumCollection = new Collection([]);
        }

        return $this->CriteriumCollection;
    }

    public function setCriteriumCollection(CollectionInterface $CriteriumCollection)
    {
        $this->CriteriumCollection = $CriteriumCollection;
    }

    public function getGlue(): ?string
    {
        return $this->glue;
    }

    public function resetGlue(): void
    {
        $this->glue = null;
    }

    public function glueByAnd(): void
    {
        $this->glue = CriteriaBuilder::GLUE_AND;
    }

    public function glueByOr(): void
    {
        $this->glue = CriteriaBuilder::GLUE_OR;
    }
}
