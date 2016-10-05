<?php
namespace Kof\Phalcon\Cache\Psr6;

use Psr\Cache\CacheItemInterface;
use DateTimeInterface;
use DateInterval;
use DateTime;

final class CacheItem implements CacheItemInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var null|string
     */
    protected $value;

    /**
     * @var int|null
     */
    protected $expiration = null;

    /**
     * @var int|null
     */
    protected $defaultLifetime = null;

    /**
     * CacheItem constructor.
     * @param string $key
     * @param null|string $value
     */
    public function __construct($key, $value = null)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        return $this->value !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration)
    {
        if (null === $expiration) {
            $this->expiration = $this->defaultLifetime > 0 ? time() + $this->defaultLifetime : null;
        } elseif ($expiration instanceof DateTimeInterface) {
            $this->expiration = (int) $expiration->format('U');
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Expiration date must implement DateTimeInterface or be null,
                     "%s" given', is_object($expiration) ? get_class($expiration) : gettype($expiration)
                )
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time)
    {
        if (null === $time) {
            $this->expiration = $this->defaultLifetime > 0 ? time() + $this->defaultLifetime : null;
        } elseif ($time instanceof DateInterval) {
            $this->expiration = (int) DateTime::createFromFormat('U', time())->add($time)->format('U');
        } elseif (is_int($time)) {
            $this->expiration = $time + time();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Expiration date must be an integer, a DateInterval or null, "%s" given',
                    is_object($time) ? get_class($time) : gettype($time)
                )
            );
        }

        return $this;
    }

    /**
     * @param $defaultLifetime
     * @return $this
     */
    public function setDefaultLifetime($defaultLifetime)
    {
        $this->defaultLifetime = $defaultLifetime;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Validates a cache key according to PSR-6.
     *
     * @param string $key The key to validate
     *
     * @throws InvalidArgumentException When $key is not valid.
     */
    public static function validateKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(sprintf(
                'Cache key must be string, "%s" given', is_object($key) ? get_class($key) : gettype($key)
            ));
        }
        if (!isset($key[0])) {
            throw new InvalidArgumentException('Cache key length must be greater than zero');
        }
        if (isset($key[strcspn($key, '{}()/\@:')])) {
            throw new InvalidArgumentException(sprintf('Cache key "%s" contains reserved characters {}()/\@:', $key));
        }
    }
}
