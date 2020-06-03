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
		'perceel_oppervlakte',
		'inhoud',
		'aantal_verdiepingen',
		'kamers',
		'slaapkamers',
		'zonligging',
		'bouwjaar',
		'onderhoud_binnen',
		'onderhoud_buiten',
		'woning_soort',
		'woning_type',
		'open_portiek',
		'woning_woonlaag',
		'hoofdtuin_type',
		'hoofdtuin_afmetingen',
		'hoofdtuin_oppervlakte',
		'has_tuin',
		'has_balkon',
		'has_garage',
		'has_zolder',
	];
}