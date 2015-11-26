<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <EveronFramework@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Dependency;

use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;

trait CriteriaBuilderFactoryWorker
{
    /**
     * @var CriteriaBuilderFactoryWorkerInterface
     */
    protected $CriteriaBuilderFactoryWorker;


    /**
     * @return CriteriaBuilderFactoryWorkerInterface
     */
    public function getCriteriaBuilderFactoryWorker()
    {
        return $this->CriteriaBuilderFactoryWorker;
    }

    /**
     * @param CriteriaBuilderFactoryWorkerInterface $FactoryWorker
     */
    public function setCriteriaBuilderFactoryWorker(CriteriaBuilderFactoryWorkerInterface $FactoryWorker)
    {
        $this->CriteriaBuilderFactoryWorker = $FactoryWorker;
    }

}
