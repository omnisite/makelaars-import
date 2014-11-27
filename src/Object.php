<?php

namespace MakelaarsImport;

class Object extends Model
{
	/**
	 * Fillable attributes
	 * @var array
	 */
	protected $fillable = [
		'tiaraId',
		'prijs',
		'prijs_conditie',
		'prijs_voorvoegsel',
		'straatnaam',
		'huisnummer',
		'postcode',
		'woonplaats',
		'land',
		'type',
		'beschikbaarheid',
		'datum_aanmelding',
		'datum_wijziging',
		'bouwvorm',
		'tekst',
		'media',
	];

	private $hash;

	public function setHash($rawObject)
	{
		$this->hash = sha1(json_encode($rawObject));
	}

	public function getHash()
	{
		return $this->hash;
	}
}
