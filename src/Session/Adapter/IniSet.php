<?php
namespace Kof\Phalcon\Session\Adapter;

use Phalcon\Session\Adapter;
use Phalcon\Session\AdapterInterface;

class IniSet extends Adapter implements AdapterInterface
{
    protected static $available_inis = array(
        'save_path' => 1, 'name' => 1, 'save_handler' => 1,
        'gc_probability' => 1, 'gc_divisor' => 1, 'gc_maxlifetime' => 1,
        'serialize_handler' => 1, 'cookie_lifetime' => 1, 'cookie_path' => 1,
        'cookie_domain' => 1, 'cookie_secure' => 1, 'cookie_httponly' => 1,
        'use_strict_mode' => 1, 'use_cookies' => 1, 'use_only_cookies' => 1,
        'referer_check' => 1, 'entropy_file' => 1, 'entropy_length' => 1,
        'cache_limiter' => 1, 'cache_expire' => 1, 'use_trans_sid' => 1,
        'bug_compat_42' => 1, 'bug_compat_warn' => 1, 'hash_function' => 1,
        'hash_bits_per_character' => 1, 'tags' => 1
    );

    /**
     * Class constructor.
     *
     * @param  array                      $options
     * @throws \Phalcon\Session\Exception
     */
    public function __construct($options = null)
    {
		if (is_array($options)) {
			foreach ($options as $key => $value) {
                if (isset(self::$available_inis[$key])) {
                    ini_set(($key == 'tags' ? 'url_rewriter' : 'session') . '.' . $key, $value);
                }
			}
		}

        parent::__construct($options);
    }
}
