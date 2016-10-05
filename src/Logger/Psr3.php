<?php
namespace Kof\Phalcon\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel as PsrLogLevel;
use Psr\Log\InvalidArgumentException;
use Phalcon\Logger as PhalconLogLevel;
use Phalcon\Logger\AdapterInterface;

class Psr3 extends AbstractLogger
{
    /**
     * The logger adapter instance.
     *
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var array Available log levels
     */
    protected $levels = array(
        PsrLogLevel::EMERGENCY => PhalconLogLevel::EMERGENCY,
        PsrLogLevel::ALERT     => PhalconLogLevel::ALERT,
        PsrLogLevel::CRITICAL  => PhalconLogLevel::CRITICAL,
        PsrLogLevel::ERROR     => PhalconLogLevel::ERROR,
        PsrLogLevel::WARNING   => PhalconLogLevel::WARNING,
        PsrLogLevel::NOTICE    => PhalconLogLevel::NOTICE,
        PsrLogLevel::INFO      => PhalconLogLevel::INFO,
        PsrLogLevel::DEBUG     => PhalconLogLevel::DEBUG,
    );

    /**
     * Constructor
     *
     * @param AdapterInterface $logger
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        if (!isset($this->levels[$level])) {
            throw new InvalidArgumentException(sprintf(
                '$level must be one of PSR-3 log levels; received %s',
                var_export($level, 1)
            ));
        }

        $this->adapter->log($this->levels[$level], $message, $context);
    }
}
