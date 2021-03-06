<?php

namespace PhpPlus\Common;


/**
 * 数组对象plus
 * @package PhpPlus\Common
 */
class ObjectArray implements
    \ArrayAccess,
    \Countable,
    \IteratorAggregate,
    \Serializable,
    \JsonSerializable
{
    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * set data
     *
     * @param mixed $data 数据数组
     */
    public function __construct($data = [])
    {
        $this->data = (array) $data;
    }

    /**
     * Return all items
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Merge data
     *
     * @param array $data
     *
     * @return array
     */
    public function merge($data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this->all();
    }

    /**
     * To determine whether the specified element exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return Arr::get($this->data, $key) != null;
    }

    /**
     * Retrieve the first item.
     *
     * @return mixed
     */
    public function first()
    {
        return reset($this->data);
    }

    /**
     * Retrieve the last item.
     *
     * @return bool
     */
    public function last()
    {
        $end = end($this->data);

        reset($this->data);

        return $end;
    }

    /**
     * add the item value.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function add($key, $value)
    {
        Arr::set($this->data, $key, $value);
    }

    /**
     * Set the item value.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        Arr::set($this->data, $key, $value);
    }

    /**
     * Retrieve item from Bag.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * Remove item form Bag
     *
     * @param string $key
     */
    public function forget($key)
    {
        Arr::forget($this->data, $key);
    }

    /**
     * 返回数组
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * 返回json
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->all());
    }

    /**
     * 返回string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
         return $this->data;
    }

    /**
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * @see Serializable::unserialize()
     */
    public function unserialize($data)
    {
        return $this->data = unserialize($data);
    }

    /**
     * @see ArrayIterator::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @see Countable::count()
     *
     * @param int $mode
     * @return int
     */
    public function count($mode = COUNT_NORMAL)
    {
        return count($this->data, $mode);
    }

    /**
     * Get a data by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Assigns a value to the specified data
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Whether or not an data exists by key
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * Unsets an data by key
     *
     * @param string $key
     */
    public function __unset($key)
    {
        $this->forget($key);
    }

    /**
     * var_export
     *
     * @return array
     */
    public function __set_state()
    {
        return $this->all();
    }

    /**
     * Assigns a value to the specified offset
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Whether or not an offset exists
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Unsets an offset
     *
     * @param string $offset
     *
     * @return array
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->forget($offset);
        }
    }

    /**
     * Returns the value at specified offset
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->get($offset) : null;
    }
}