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

use Everon\Component\Utils\Collection\ArrayableInterface;

interface SqlPartInterface extends ArrayableInterface
{
    public function getParameters();

    public function setParameters(array $parameters);

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setParameterValue(string $name, $value): void;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getParameterValue(string $name);

    public function getSql(): string;

    public function setSql(string $sql): void;
}
