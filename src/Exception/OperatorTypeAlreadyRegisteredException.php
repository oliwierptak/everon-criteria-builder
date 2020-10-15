<?php declare(strict_types = 1);
/**
 * This file is part of the Everon components.
 *
 * (c) Oliwier Ptak <everonphp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Component\CriteriaBuilder\Exception;

use Everon\Component\Utils\Exception\AbstractException;

class OperatorTypeAlreadyRegisteredException extends AbstractException
{

    protected $message = 'Operator type "%s" is already registered';

}
