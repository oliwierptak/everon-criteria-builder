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

use Everon\Component\Utils\Collection\ArrayableInterface;

interface SqlPartInterface extends ArrayableInterface
{

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters);

    /**
     * @param string $name
     * @param $value
     */
    public function setParameterValue(string $name, $value);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getParameterValue(string $name);

    /**
     * @return string
     */
    public function getSql(): string;

    /**
     * @param string $sql
     */
    public function setSql(string $sql);

}
