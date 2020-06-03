<?php

namespace MakelaarsImport\Vendor;

use MakelaarsImport\WoonObject;
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

			$woningType = $pObject->Wonen->Appartement ? 'appartement' : 'woonhuis';

			$object = new WoonObject([
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
				'korte_omschrijving' => (string) $pObject->Web->KorteOmschrijving,
			]);

			// set hash, used for comparing object
			$object->setHash($pObject);

			// find wonen
			$wonen = $pObject->Wonen;

			$ruimten = isset($wonen->Woonlagen->BeganeGrondOfFlat->OverigeRuimten->OverigeRuimte) ? (array) $wonen->Woonlagen->BeganeGrondOfFlat->OverigeRuimten->OverigeRuimte : [];

			$hasTuin = ('geen tuin' != (string) $wonen->WonenDetails->Tuin->Tuintypen->Tuintype) ? true : false;
			$hasGarage = ('geen garage' != (string) $wonen->WonenDetails->Garage->Soorten->Soort) ? true : false;
			$hasBalkon = (bool) in_array('balkon', array_map(function($ruimte) {
				return 	$ruimte;
			}, $ruimten));
			$wonenValues = [
				'woon_oppervlakte' => (string) $wonen->WonenDetails->MatenEnLigging->GebruiksoppervlakteWoonfunctie,
				'perceel_oppervlakte' => (string) $wonen->WonenDetails->MatenEnLigging->PerceelOppervlakte,
				'inhoud' => (string) $wonen->WonenDetails->MatenEnLigging->Inhoud,
				'aantal_verdiepingen' => (string) $wonen->Verdiepingen->Aantal,
				'kamers' => (string) $wonen->Verdiepingen->AantalKamers,
				'slaapkamers' => (string) $wonen->Verdiepingen->AantalSlaapKamers,
				'zonligging' => (string) $wonen->WonenDetails->Hoofdtuin->Positie,
				'bouwjaar' => (string) $wonen->WonenDetails->Bouwjaar->JaarOmschrijving->Jaar,
				'onderhoud_binnen' => (string) $wonen->WonenDetails->Onderhoud->Binnen->Waardering,
				'onderhoud_buiten' => (string) $wonen->WonenDetails->Onderhoud->Buiten->Waardering,
				'has_tuin' => $hasTuin,
				'has_balkon' => $hasBalkon,
				'has_garage' => $hasGarage,
				'has_zolder' => (bool) $wonen->Woonlagen->Zolder,
			];

			if ('woonhuis' == $woningType) {
				$wonenValues['woning_soort'] = (string) $wonen->{ucfirst($woningType)}->SoortWoning;
				$wonenValues['woning_type'] = (string) $wonen->{ucfirst($woningType)}->TypeWoning;
			} elseif ('appartement' == $woningType) {
				$wonenValues['woning_soort'] = (string) $wonen->{ucfirst($woningType)}->SoortAppartement;
				$wonenValues['open_portiek'] = (string) $wonen->{ucfirst($woningType)}->OpenPortiek;
				$wonenValues['woning_woonlaag'] = (string) $wonen->{ucfirst($woningType)}->Woonlaag;
			}

			if ($wonenValues['has_tuin']) {
				if ($wonen->WonenDetails->Hoofdtuin) {
					$wonenValues['hoofdtuin_type'] = (string) $wonen->WonenDetails->Hoofdtuin->Type;
					$wonenValues['hoofdtuin_afmetingen'] = (string) $wonen->WonenDetails->Hoofdtuin->Afmetingen->Lengte . 'x' . (string) $wonen->WonenDetails->Hoofdtuin->Afmetingen->Breedte;
					$wonenValues['hoofdtuin_oppervlakte'] = (string) $wonen->WonenDetails->Hoofdtuin->Afmetingen->Oppervlakte;
				} else {
					$tuintype = (string) $wonen->WonenDetails->Tuin->Tuintypen->Tuintype;
					if (in_array($tuintype, ['zonneterras'])) {
						$wonenValues['hoofdtuin_type'] = (string) $wonen->WonenDetails->Hoofdtuin->Type;
						$wonenValues['has_tuin'] = false;
						$wonenValues['has_balkon'] = true;
					}
				}
			}

			$object['wonen'] = new Wonen($wonenValues);

			// find media
			foreach ($pObject->MediaLijst->Media as $mediaItem) {
				$media = new Media([
					'type' => (string) $mediaItem->Groep,
					'raw_url' => (string) $mediaItem->URL,
					'datum_wijziging' => (string) $mediaItem->LaatsteWijziging,
				]);

				$object->media[] = $media;
			}

			// add object to objects array
			$objects[] = $object;
		}

		return $objects;
	}
}
