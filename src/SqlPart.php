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

use Everon\Component\Utils\Collection\ToArray;

class SqlPart implements SqlPartInterface
{

    use ToArray;

    /**
     * @var string
     */
    protected $sql = null;

    /**
     * @var array
     */
    protected $parameters = null;

    /**
     * @param $sql
     * @param $parameters
     */
    public function __construct($sql, $parameters)
    {
        $this->sql = $sql;
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    protected function getArrayableData()
    {
        return [
            'sql' => $this->getSql(),
            'parameters' => $this->getParameters(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function setParameterValue($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function getParameterValue($name)
    {
        return $this->parameters[$name];
    }

    /**
     * @inheritdoc
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @inheritdoc
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @inheritdoc
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @inheritdoc
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
    }

}
