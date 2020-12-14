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
namespace DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Formatter;

use DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger;
/**
 * Formats a log message according to the ChromePHP array format
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ChromePHPFormatter implements \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Formatter\FormatterInterface
{
    /**
     * Translates Monolog log levels to Wildfire levels.
     */
    private $logLevels = [\DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::DEBUG => 'log', \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::INFO => 'info', \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::NOTICE => 'info', \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::WARNING => 'warn', \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::ERROR => 'error', \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::CRITICAL => 'error', \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::ALERT => 'error', \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::EMERGENCY => 'error'];
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        // Retrieve the line and file if set and remove them from the formatted extra
        $backtrace = 'unknown';
        if (isset($record['extra']['file'], $record['extra']['line'])) {
            $backtrace = $record['extra']['file'] . ' : ' . $record['extra']['line'];
            unset($record['extra']['file'], $record['extra']['line']);
        }
        $message = ['message' => $record['message']];
        if ($record['context']) {
            $message['context'] = $record['context'];
        }
        if ($record['extra']) {
            $message['extra'] = $record['extra'];
        }
        if (count($message) === 1) {
            $message = reset($message);
        }
        return [$record['channel'], $message, $backtrace, $this->logLevels[$record['level']]];
    }
    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        $formatted = [];
        foreach ($records as $record) {
            $formatted[] = $this->format($record);
        }
        return $formatted;
    }
}
