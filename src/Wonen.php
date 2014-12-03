<?php

namespace MakelaarsImport;

class Wonen extends Model
{
	/**
	 * Fillable attributes
	 * @var array
	 */
	protected $fillable = [
		'woon_oppervlakte',
		'inhoud',
		'aantal_verdiepingen',
		'kamers',
		'slaapkamers',
		'zonligging',
	];
}