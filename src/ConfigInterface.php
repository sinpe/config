<?php
/*
 * This file is part of the long/config package.
 *
 * (c) Sinpe <support@sinpe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sinpe\Config;

/**
 * Configuration interface
 */
interface ConfigInterface
{
    /**
     * Determine if a config exists in the sets by key.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function has($key);

    /**
     * Get values.
     *
     * @param array|string $key     a key or a sets.
     * @param mixed        $default when key not exists, return the value.
     * 
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Set configuration values.
     *
     * @param array|string $key   a key or a value sets.
     * @param mixed        $value when $key is a key, put value here.
     * 
     * @return void
     */
    public function set($key, $value = null);
}
