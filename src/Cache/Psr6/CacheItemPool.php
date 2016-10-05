<?php
namespace Kof\Phalcon\Cache\Psr6;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Phalcon\Cache\BackendInterface as PhalconCache;
use Exception;

class CacheItemPool implements CacheItemPoolInterface
{
    /**
     * @var PhalconCache
     */
    protected $phalconCache;

    /**
     * @var CacheItem[]
     */
    protected $deferred = [];

    /**
     * CacheItemPool constructor.
     * @param PhalconCache $phalconCache
     */
    public function __construct(PhalconCache $phalconCache)
    {
        $this->phalconCache = $phalconCache;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        CacheItem::validateKey($key);

        try {
            $value = $this->phalconCache->get($key);
        } catch (Exception $e) {
            return new CacheItem($key, null, false);
        }

        return new CacheItem($key, $value, $value !== null);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = array())
    {
        $items = array();
        foreach ($keys as $key) {
            $item = $this->getItem($key);
            $items[$key] = $item;
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        $item = $this->getItem($key);

        return $item->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->phalconCache->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        CacheItem::validateKey($key);

        try {
            return $this->phalconCache->delete($key);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return $this->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $now = time();
        foreach ($this->deferred as $key => $item) {
            $expiration = $item->getExpiration();
            $lifetime = $expiration - $now;
            try {
                if (!$this->phalconCache->save($item->getKey(), $item->get(), $lifetime)) {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }

            unset($this->deferred[$key]);
        }

        return true;
    }
}
