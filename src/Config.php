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
 * Configuration.
 * 
 * @package Sinpe\Config
 * @since   1.0.0
 */
class Config implements ConfigInterface, \ArrayAccess
{
    /**
     * Paths.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Files loaded.
     *
     * @var array
     */
    protected $loaded = [];

    /**
     * Loaded callback.
     *
     * @var callback
     */
    protected $callback;

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
     * Set paths for config file when search.
     *
     * @param  string  $key
     * @param  bool  $append
     * @return static
     */
    public function setPath(string $path, bool $append = false)
    {
        $path = rtrim($path, '\\/');

        if ($append) {
            $this->paths[] = $path;
        } else {
            $this->paths = [$path];
        }

        return $this;
    }

    /**
     * Set the callback after file loaded.
     *
     * @param  callable  $callback
     * @return static
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Load config from file.
     *
     * @param  string  $file
     * @return static
     */
    public function load(string $file)
    {
        if (isset($this->loaded[$file])) {
            return $this;
        }

        // full path
        if (file_exists($file)) {

            $datas = include_once $file;

            if (is_callable($this->callback)) {
                $datas = call_user_func($this->callback, $datas);
            } else {
                if (is_callable($datas)) {
                    $datas = call_user_func($datas);
                }
            }

            $this->loaded[$file] = true;

            if (!is_null($datas)) {

                $pathParts = pathinfo($file);

                $this->set($pathParts['filename'], $datas);
            }
        } else {
            $file = trim($file, '\\/') . '.php';

            foreach ($this->paths as $path) {

                $fullFile = $path . '/' . $file;

                if (file_exists($fullFile)) {
                    $this->load($fullFile);
                }
            }
        }

        return $this;
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

        return static::hasData($this->items, $keys);
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

        $value = static::getData($this->items, $key, $default);

        if (is_null($value)) {
            // load
            $keys = explode('.', $key);

            $this->load($keys[0]);

            $value = $this->get($key, $default);
        }

        return $value;
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
            $config[$key] = $this->get($key, $default);
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
            static::setData($this->items, $key, $value);
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

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array  $data
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    private static function getData(array $data, string $key = '', $default = null)
    {
        if (empty($key)) {
            return $data;
        }

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        if (strpos($key, '.') === false) {
            return $data[$key] ?? value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (array_key_exists($segment, $data)) {
                $data = $data[$segment];
            } else {
                return value($default);
            }
        }

        return $data;
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  array  $data
     * @param  string|array  $keys
     * @return bool
     */
    private static function hasData(array $data, $keys)
    {
        $keys = (array) $keys;

        if (!$data || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {

            if (array_key_exists($key, $data)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (array_key_exists($segment, $data)) {
                    $data = $data[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $data
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    private static function setData(array &$data, string $key = '', $value = null)
    {
        if (empty($key)) {
            return $data;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            // 
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
            }

            $data = &$data[$key];
        }

        $data[array_shift($keys)] = $value;

        return $data;
    }
}
