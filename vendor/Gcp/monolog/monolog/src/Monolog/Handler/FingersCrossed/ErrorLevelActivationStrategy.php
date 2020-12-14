<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Handler\FingersCrossed;

use DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger;
/**
 * Error level based activation strategy.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ErrorLevelActivationStrategy implements \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Handler\FingersCrossed\ActivationStrategyInterface
{
    /**
     * @var int
     */
    private $actionLevel;
    /**
     * @param int|string $actionLevel Level or name or value
     */
    public function __construct($actionLevel)
    {
        $this->actionLevel = \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::toMonologLevel($actionLevel);
    }
    public function isHandlerActivated(array $record) : bool
    {
        return $record['level'] >= $this->actionLevel;
    }
}
