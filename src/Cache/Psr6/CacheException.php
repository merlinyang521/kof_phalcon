<?php
namespace Kof\Phalcon\Cache\Psr6;

use Psr\Cache\CacheException as PsrCacheException;

class CacheException extends \Exception implements PsrCacheException
{

}
