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

use Everon\Component\CriteriaBuilder\Dependency\CriteriaBuilderFactoryWorkerAwareInterface;
use Everon\Component\CriteriaBuilder\SqlPartInterface;
use Everon\Component\Utils\Collection\ArrayableInterface;

interface CriteriumInterface extends ArrayableInterface, CriteriaBuilderFactoryWorkerAwareInterface
{

    public function getColumn(): string;

    public function setColumn(string $column);

    public function getOperatorType(): string;

    public function setOperatorType(string $operator);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     */
    public function setValue($value);

    public function getGlue(): ?string;

    public function glueByAnd(): void;

    public function glueByOr(): void;

    public function resetGlue(): void;

    public function getPlaceholder(): string;

    public function setPlaceholder(string $placeholder);

    public function getPlaceholderAsParameter(): string;

    /**
     * @return \Everon\Component\CriteriaBuilder\SqlPartInterface
     * @throws \Everon\Component\CriteriaBuilder\Exception\UnknownOperatorTypeException
     */
    public function getSqlPart(): SqlPartInterface;

    public function setSqlPart(SqlPartInterface $SqlPart);

}
