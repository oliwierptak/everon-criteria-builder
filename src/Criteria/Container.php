<?php declare(strict_types = 1);
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

    public function __construct(CriteriaInterface $Criteria, ?string $glue = null)
    {
        $this->Criteria = $Criteria;
        $this->glue = $glue;
    }

    public function getCriteria(): CriteriaInterface
    {
        return $this->Criteria;
    }

    public function setCriteria(CriteriaInterface $Criteria)
    {
        $this->Criteria = $Criteria;
    }

    public function getGlue(): ?string
    {
        return $this->glue;
    }

    public function setGlue(?string $glue)
    {
        $this->glue = $glue;
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
