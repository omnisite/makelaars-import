<?php

namespace MakelaarsImport\Vendor;

use MakelaarsImport\Object;

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
		'tiaraId' => 'ObjectTiaraID',
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

		foreach ($parser['value'] as $pObject) {
			$object = new Object;
			// var_dump($pObject);
			foreach ($pObject as $data) {
				if (is_array($data) && $data) {
					$attributes = $this->searchForAttributes($data);
				}
			}

			$object->fill($attributes);
			var_dump($object);

			// add object to objects array
			$objects[] = $object;
		}

		return $objects;
	}

	private function searchForAttributes($data)
	{
		$attributes = [];
		foreach($data as $element) {
			// var_dump($element);
			if ($attribute = $this->inMap($element['name'])) {
				$attributes[$attribute] = $element['value'];
			}

			if (is_array($element['value'])) {
				$attributes = array_merge($attributes, $this->searchForAttributes($element['value']));
			}
		}
// var_dump($attributes);
		return $attributes;
	}

	private function inMap($elementName)
	{
		if ($k = array_search(preg_replace('/\{.*\}/', '', $elementName), $this->map)) {
			return $k;
		}
	}
}