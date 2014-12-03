<?php

namespace MakelaarsImport;

class Object extends Model
{
	/**
	 * Fillable attributes
	 * @var array
	 */
	protected $fillable = [
		'vendor_id',
		'tiara_id',
		'prijs_type',
		'prijs',
		'prijs_conditie',
		'prijs_voorvoegsel',
		'straatnaam',
		'huisnummer',
		'postcode',
		'woonplaats',
		'land',
		'woning_type',
		'status',
		'datum_aanmelding',
		'datum_wijziging',
		'bouwvorm',
		'tekst',
		'wonen',
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
