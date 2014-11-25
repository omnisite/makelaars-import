<?php

namespace Makelaarsimport;

class Model
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
}
