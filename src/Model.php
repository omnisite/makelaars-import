<?php

namespace Makelaarsimport;

class Model implements \ArrayAccess
{
	public function __construct($attributes = [])
	{
		$this->fill($attributes);
	}

	public function fill($attributes)
	{
		foreach ($attributes as $attribute => $value) {
			if ($this->isFillable($attribute)) {
				$this->{$attribute} = $value;
			}
		}
	}

	public function isFillable($attribute)
	{
		return in_array($attribute, $this->fillable);
	}

	public function get($key) {
        return $this->{$key};
	}

	public function set($key, $value) {
		if ($this->isFillable($key)) {
			$this->{$key} = $value;
		}
	}

	public function has($key) {
		if ($this->{$key}) {
			return true;
		}

		return false;
	}

	/**
     * Alias method for get().
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key) {
        return $this->get($key);
    }
    /**
     * Alias method for set().
     *
     * @param string $key
     * @param mixed $value
     */
    public function offsetSet($key, $value) {
        $this->set($key, $value);
    }
    /**
     * Alias method for has().
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key) {
        return $this->has($key);
    }
    /**
     * Alias method for remove().
     *
     * @param string $key
     */
    public function offsetUnset($key) {
        $this->remove($key);
    }
}
