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
    public function getParameters();

    /**
     * @param array $parameters
     */
    public function setParameters($parameters);

    /**
     * @param $name
     * @param $value
     */
    public function setParameterValue($name, $value);

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getParameterValue($name);

    /**
     * @return string
     */
    public function getSql();

    /**
     * @param string $sql
     */
    public function setSql($sql);

}
