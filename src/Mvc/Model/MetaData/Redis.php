<?php
namespace Kof\Phalcon\Mvc\Model\MetaData;

use Phalcon\Mvc\Model\MetaData\Redis as RedisMetaData;

class Redis extends RedisMetaData
{
    public function flush()
    {
        return $this->_redis->flush();
    }
}
