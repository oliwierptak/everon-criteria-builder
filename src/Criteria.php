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
     * @return array
     */
    protected function getArrayableData($deep=false)
    {
        return $this->getCriteriumCollection()->toArray($deep);
    }

    /**
     * @inheritdoc
     */
    public function where(CriteriumInterface $Criterium)
    {
        $Criterium->resetGlue();
        $this->getCriteriumCollection()->append($Criterium);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function andWhere(CriteriumInterface $Criterium)
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
    public function orWhere(CriteriumInterface $Criterium)
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
    public function getCriteriumCollection()
    {
        if ($this->CriteriumCollection === null) {
            $this->CriteriumCollection = new Collection([]);
        }

        return $this->CriteriumCollection;
    }

    /**
     * @inheritdoc
     */
    public function setCriteriumCollection(CollectionInterface $CriteriumCollection)
    {
        $this->CriteriumCollection = $CriteriumCollection;
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
    public function resetGlue()
    {
        $this->glue = null;
    }

    /**
     * @inheritdoc
     */
    public function glueByAnd()
    {
        $this->glue = CriteriaBuilder::GLUE_AND;
    }

    /**
     * @inheritdoc
     */
    public function glueByOr()
    {
        $this->glue = CriteriaBuilder::GLUE_OR;
    }

}
