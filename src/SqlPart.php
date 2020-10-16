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

    public function __construct($sql, $parameters)
    {
        $this->sql = $sql;
        $this->parameters = $parameters;
    }

    protected function getArrayableData(): array
    {
        return [
            'sql' => $this->getSql(),
            'parameters' => $this->getParameters(),
        ];
    }

    public function setParameterValue(string $name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function getParameterValue(string $name)
    {
        return $this->parameters[$name];
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function setSql(string $sql): void
    {
        $this->sql = $sql;
    }
}
