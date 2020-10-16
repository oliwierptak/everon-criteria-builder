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

use Everon\Component\CriteriaBuilder\CriteriaInterface;

interface ContainerInterface
{
    public function getCriteria(): CriteriaInterface;

    public function setCriteria(CriteriaInterface $Criteria);

    public function getGlue(): ?string;

    public function setGlue(?string $glue);

    public function resetGlue(): void;

    public function glueByAnd(): void;

    public function glueByOr(): void;

}
