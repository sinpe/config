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

use Illuminate\Support\Arr;

/**
 * Configuration.
 * 
 * @package Sinpe\Config
 * @since   1.0.0
 */
class Config implements ConfigInterface, \ArrayAccess
{
    /**
     * Values.
     *
     * @var array
     */
    protected $items = [];

    /**
     * __construct
     *
     * @param array $items default values.
     * 
     * @return void
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Determine if a config exists in the sets by key.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        return Arr::has($this->items, $key);
    }

    /**
     * Get one value or multi values.
     *
     * @param array|string $key     a key or a sets.
     * @param mixed        $default when key not exists, return the value.
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        return Arr::get($this->items, $key, $default);
    }

    /**
     * Get multi values once.
     *
     * @param array $keys a map of keys and default values.
     * 
     * @return array
     */
    public function getMany($keys)
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                list($key, $default) = [$default, null];
            }
            $config[$key] = Arr::get($this->items, $key, $default);
        }

        return $config;
    }

    /**
     * Set configuration values.
     *
     * @param array|string $key   a key or a value sets.
     * @param mixed        $value when $key is a key, put value here.
     * 
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->items, $key, $value);
        }
    }

    /**
     * Get all of the configs in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Remove a config from the sets by keys.
     *
     * @param  string|array  $keys
     * @return $this
     */
    public function remove($keys)
    {
        foreach ((array) $keys as $key) {
            $this->offsetUnset($key);
        }
        return $this;
    }

    /**
     * Determine if a config exists at a key.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a config at a given key.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the config at a given key.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }
}
