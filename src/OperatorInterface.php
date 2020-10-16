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

use Everon\Component\CriteriaBuilder\Criteria\CriteriumInterface;

interface OperatorInterface
{
    public function getType(): string;

    public function getTypeAsSql(): string;

    public function toSqlPartData(CriteriumInterface $Criterium): array;

}
