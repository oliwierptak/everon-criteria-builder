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

use Everon\Component\Utils\Collection\ToArray;

class SqlPart implements SqlPartInterface
{

    use ToArray;

    /**
     * @var string
     */
    protected $sql;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param string $sql
     * @param array $parameters
     */
    public function __construct(string $sql, array $parameters)
    {
        $this->sql = $sql;
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    protected function getArrayableData(): array
    {
        return [
            'sql' => $this->getSql(),
            'parameters' => $this->getParameters(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function setParameterValue(string $name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function getParameterValue(string $name)
    {
        return $this->parameters[$name];
    }

    /**
     * @inheritdoc
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @inheritdoc
     */
    public function getSql(): string
    {
        return (string) $this->sql;
    }

    /**
     * @inheritdoc
     */
    public function setSql(string $sql)
    {
        $this->sql = $sql;
    }

}
