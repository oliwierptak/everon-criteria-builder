<?php
/**
 * This file is part of the Everon components.
 *
 * (c) Oliwier Ptak <everonphp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Dependency;

use Everon\Component\CriteriaBuilder\CriteriaBuilderFactoryWorkerInterface;

interface CriteriaBuilderFactoryWorkerAwareInterface
{

    /**
     * @return CriteriaBuilderFactoryWorkerInterface
     */
    public function getCriteriaBuilderFactoryWorker();

    /**
     * @param CriteriaBuilderFactoryWorkerInterface $FactoryWorker
     */
    public function setCriteriaBuilderFactoryWorker(CriteriaBuilderFactoryWorkerInterface $FactoryWorker);

}
