<?php

namespace MakelaarsImport\Vendor;

use MakelaarsImport\Object;
use MakelaarsImport\Media;
use MakelaarsImport\Wonen;

class Realworks extends \MakelaarsImport\Vendor
{
	/**
	 * RealWorks import url
	 * @var string
	 */
	private $url = 'https://xml-publish.realworks.nl/servlets/ogexport';
	/**
	 * Koppeling argument
	 * @var string
	 */
	private $koppeling = 'WEBSITE';
	/**
	 * User name to connect with Realworks
	 * @var string
	 */
	private $user;
	/**
	 * Password needed for connection
	 * @var string
	 */
	private $password;
	/**
	 * OG type
	 * @var string
	 */
	private $og = 'WONEN';

	private $map = [
		'vendor_id' => 'ObjectCode',
		'tiara_id' => 'ObjectTiaraID',
		'prijs' => 'Koopprijs',
		'prijs_conditie' => 'KoopConditie',
		'prijs_voorvoegsel' => 'Prijsvoorvoegsel',
		'straatnaam' => 'Straatnaam',
		'huisnummer' => 'Huisnummer',
		'postcode' => 'Postcode',
		'woonplaats' => 'Woonplaats',
		'land' => 'Land',
		'status' => 'Status',
		'datum_aanmelding' => 'DatumInvoer',
		'datum_wijziging' => 'DatumWijziging',
		'bouwvorm' => 'Bouwvorm',
		'tekst' => 'Aanbiedingstekst',
	];

	/**
	 * Constructor
	 * @param array $arguments
	 */
	public function __construct($arguments = [])
	{
		if ($arguments) {
			$this->setArguments($arguments);
		}
	}

	/**
	 * Magic setter
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		if (property_exists($this, $name)) {
			$this->{$name} = $value;
		}

		return $this;
	}

	/**
	 * Sets arguments
	 * @param array $arguments
	 */
	public function setArguments($arguments)
	{
		foreach ($arguments as $argument => $value) {
			$this->{$argument} = $value;
		}

		return $this;
	}

	/**
	 * Returns url
	 * @return string
	 */
	public function getUrl()
	{
		$arguments = [
			'koppeling' => $this->koppeling,
			'user' => $this->user,
			'password' => $this->password,
			'og' => $this->og,
		];

		return $this->url . '?' . implode('&', array_map(function($arg, $value) {
			return $arg . '=' . $value;
		}, array_keys($arguments), array_values($arguments)));
	}

	public function parse($parser)
	{
		$objects = [];

		foreach ($parser->Object as $pObject) {
			// find global 
			$details = $pObject->ObjectDetails;
			$prijsType = $details->Huur ? 'huur' : 'koop';
			$prijsObject = $details->{ucfirst($prijsType)};

			$woningType = $details->Appartement ? 'appartement' : 'woonhuis';

			$object = new Object([
				'vendor_id' => (string) $pObject->ObjectCode,
				'tiara_id' => (string) $pObject->ObjectTiaraID,
				'prijs_type' => (string) $prijsType,
				'prijs' => (string) $prijsObject->{ucfirst($prijsType) . 'prijs'},
				'prijs_conditie' => (string) $prijsObject->{ucfirst($prijsType) . 'Conditie'},
				'prijs_voorvoegsel' => (string) $prijsObject->Prijsvoorvoegsel,
				'straatnaam' => (string) $details->Adres->Nederlands->Straatnaam,
				'huisnummer' => (string) $details->Adres->Nederlands->Huisnummer,
				'postcode' => (string) $details->Adres->Nederlands->Postcode,
				'woonplaats' => (string) $details->Adres->Nederlands->Woonplaats,
				'land' => (string) $details->Adres->Nederlands->Land,
				'woning_type' => (string) $woningType,
				'status' => (string) $details->StatusBeschikbaarheid->Status,
				'datum_aanmelding' => (string) $details->DatumInvoer,
				'datum_wijziging' => (string) $details->DatumWijziging,
				'bouwvorm' => (string) $details->Bouwvorm,
				'tekst' => (string) $details->Aanbiedingstekst,
			]);

			// set hash, used for comparing object
			$object->setHash($pObject);

			// find wonen
			$wonen = $pObject->Wonen;
			$object['wonen'] = new Wonen([
				'woon_oppervlakte' => (string) $wonen->WonenDetails->MatenEnLigging->GebruiksoppervlakteWoonfunctie,
				'inhoud' => (string) $wonen->WonenDetails->MatenEnLigging->Inhoud,
				'aantal_verdiepingen' => (string) $wonen->Verdiepingen->Aantal,
				'kamers' => (string) $wonen->Verdiepingen->AantalKamers,
				'slaapkamers' => (string) $wonen->Verdiepingen->AantalSlaapKamers,
				'zonligging' => (string) $wonen->WonenDetails->Hoofdtuin->Positie,
			]);

			// find media
			foreach ($pObject->MediaLijst->Media as $mediaItem) {
				$media = new Media([
					'type' => (string) $mediaItem->Groep,
					'raw_url' => (string) $mediaItem->URL,
					'datum_wijziging' => (string) $mediaItem->LaatsteWijziging,
				]);

				$object->media[] = $media;
			}
			
			// foreach ($pObject as $data) {
			// 	if (is_array($data) && $data) {
			// 		$attributes = $this->searchForAttributes($data, $this->map);

			// 		// determine type
			// 		if ($this->searchForAttributes($data, ['appartement' => 'Appartement'])) {
			// 			$attributes['type'] = 'appartement';
			// 		} else if ($this->searchForAttributes($data, ['appartement' => 'Woonhuis'])) {
			// 			$attributes['type'] = 'woonhuis';
			// 		}

			// 		$wonen = $this->searchForAttributes($data, [
			// 			'oppervlakte' => 'GebruiksoppervlakteWoonfunctie',
			// 			'kamers' => 'Verdiepingen:AantalKamers',
			// 			'slaapkamers' => 'Verdiepingen:AantalSlaapkamers',
			// 			'zonligging' => 'Positie',
			// 		]);
			// 		$attributes['wonen'] = new Wonen($wonen);

			// 		// get media
			// 		$mediaList = $this->searchForAttributes($data, ['media' => 'MediaLijst']);
			// 		if ($mediaList) {
			// 			$media = [];
			// 			foreach ($mediaList['media'] as $mediaElement) {
			// 				$mediaAttributes = $this->searchForAttributes([$mediaElement], [
			// 					'type' => 'Groep',
			// 					'raw_url' => 'URL',
			// 					'datum_wijziging' => 'LaatsteWijziging',
			// 				]);
			// 				$mediaObject = new Media($mediaAttributes);
			// 				$media[] = $mediaObject;
			// 			}

			// 			$attributes['media'] = $media;
			// 		}
			// 	}
			// }

			// add object to objects array
			$objects[] = $object;
		}

		return $objects;
	}

	private function searchForAttributes($data, $map)
	{
		// foreach ($map as $k => $element) {
		// 	if (preg_match('/:/', &$element)) {
		// 		$element = explode(':', $element);
		// 	}
		// }

		$attributes = [];
		foreach($data as $element) {
			if ($attribute = $this->inMap($element['name'], $map)) {
				$attributes[$attribute] = $element['value'];
			}

			if (is_array($element['value'])) {
				$attributes = array_merge($attributes, $this->searchForAttributes($element['value'], $map));
			}
		}

		return $attributes;
	}

	private function inMap($elementName, $map)
	{
		if ($k = array_search(preg_replace('/\{.*\}/', '', $elementName), $map)) {
			return $k;
		}
	}
}