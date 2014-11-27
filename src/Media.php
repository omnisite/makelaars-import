<?php

namespace MakelaarsImport;

class Media extends Model
{
	/**
	 * Fillable attributes
	 * @var array
	 */
	protected $fillable = [
		'type',
		'raw_url',
		'datum_wijziging',
	];
}
