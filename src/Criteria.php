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

use Everon\Component\Collection\Collection;
use Everon\Component\Collection\CollectionInterface;
use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;
use Everon\Component\CriteriaBuilder\Exception\NoSubQueryFoundException;
use Everon\Component\Utils\Collection\ToArray;

class Criteria implements CriteriaInterface
{

    use ToArray;

    /**
     * @var CollectionInterface
     */
    protected $CriteriumCollection = null;

    /**
     * @var string
     */
    protected $glue = CriteriaBuilder::GLUE_AND;

    /**
     * @param bool $deep
     *
     * @return array
     */
    protected function getArrayableData(bool $deep = false): array
    {
        return $this->getCriteriumCollection()->toArray($deep);
    }

    /**
     * @inheritdoc
     */
    public function where(CriteriumInterface $Criterium): CriteriaInterface
    {
        $Criterium->resetGlue();
        $this->getCriteriumCollection()->append($Criterium);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function andWhere(CriteriumInterface $Criterium): CriteriaInterface
    {
        if ($this->getCriteriumCollection()->isEmpty()) {
            throw new NoSubQueryFoundException();
        }

        $Criterium->glueByAnd();
        $this->getCriteriumCollection()->append($Criterium);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function orWhere(CriteriumInterface $Criterium): CriteriaInterface
    {
        if ($this->getCriteriumCollection()->isEmpty()) {
            throw new NoSubQueryFoundException();
        }

        $Criterium->glueByOr();
        $this->getCriteriumCollection()->append($Criterium);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCriteriumCollection(): CollectionInterface
    {
        if ($this->CriteriumCollection === null) {
            $this->CriteriumCollection = new Collection([]);
        }

        return $this->CriteriumCollection;
    }

    /**
     * @inheritdoc
     */
    public function setCriteriumCollection(CollectionInterface $CriteriumCollection): CriteriaInterface
    {
        $this->CriteriumCollection = $CriteriumCollection;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGlue()
    {
        return $this->glue;
    }

    /**
     * @inheritdoc
     */
    public function resetGlue(): CriteriaInterface
    {
        $this->glue = null;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function glueByAnd(): CriteriaInterface
    {
        $this->glue = CriteriaBuilder::GLUE_AND;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function glueByOr(): CriteriaInterface
    {
        $this->glue = CriteriaBuilder::GLUE_OR;

        return $this;
    }

}
