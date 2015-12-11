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

use Everon\Component\CriteriaBuilder\CriteriaBuilder;
use Everon\Component\CriteriaBuilder\CriteriaInterface;

class Container implements ContainerInterface
{

    /**
     * @var CriteriaInterface
     */
    protected $Criteria = null;

    /**
     * @var string
     */
    protected $glue = null;

    /**
     * @param CriteriaInterface $Criteria
     * @param $glue
     */
    public function __construct(CriteriaInterface $Criteria, $glue)
    {
        $this->Criteria = $Criteria;
        $this->glue = $glue;
    }

    /**
     * @return CriteriaInterface
     */
    public function getCriteria()
    {
        return $this->Criteria;
    }

    /**
     * @param CriteriaInterface $Criteria
     */
    public function setCriteria(CriteriaInterface $Criteria)
    {
        $this->Criteria = $Criteria;
    }

    /**
     * @return string
     */
    public function getGlue()
    {
        return $this->glue;
    }

    /**
     * @param string $glue
     */
    public function setGlue($glue)
    {
        $this->glue = $glue;
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
