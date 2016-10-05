<?php
namespace Kof\Phalcon\Cache\Psr6;

use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;

class InvalidArgumentException extends \Exception implements PsrInvalidArgumentException
{

}
