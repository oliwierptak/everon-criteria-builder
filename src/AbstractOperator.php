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
use Everon\Component\Utils\Text\LastTokenToName;

abstract class AbstractOperator implements OperatorInterface
{

    use LastTokenToName;

    const TYPE_NAME = '';
    const TYPE_AS_SQL = '';

    /**
     * @var string
     */
    protected $typeAsSql;

    public function getType(): string
    {
        return static::TYPE_NAME;
    }

    public function getTypeAsSql(): string
    {
        return static::TYPE_AS_SQL;
    }

    public function toSqlPartData(CriteriumInterface $Criterium): array
    {
        $sql = sprintf('%s %s %s', $Criterium->getColumn(), $this->getTypeAsSql(), $Criterium->getPlaceholder());
        $params = [
            $Criterium->getPlaceholderAsParameter() => $Criterium->getValue(),
        ];

        if ($Criterium->getValue() === null) {
            $params = [];
        }

        return [$sql, $params];
    }

}
