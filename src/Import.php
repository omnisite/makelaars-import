<?php

namespace MakelaarsImport;

use GuzzleHttp\Client;
use Sabre\XML;

class Import
{
	/**
	 * Vendor object
	 * @var object
	 */
	private $vendor;
	/**
	 * Objects for this import
	 * @var array
	 */
	private $objects = [];

	/**
	 * Constructor
	 * @param string $vendor
	 * @param array $arguments
	 */
	public function __construct($vendor, $arguments = [])
	{
		// set vendor object
		$this->setVendor($vendor, $arguments);
	}

	/**
	 * Sets vendor property by vendor name
	 * @param string $vendor
	 * @param array $arguments
	 */
	private function setVendor($vendor, $arguments = [])
	{
		$vendorClass = '\MakelaarsImport\Vendor\\' . ucfirst(strtolower($vendor));
		$vendorObj = new $vendorClass($arguments);

		$this->vendor = $vendorObj;

		return $this;
	}

	public function run()
	{
		// download XML/zip
		$contents = $this->retrieve();

		if ($contents) {
			// parse xml
			if (!$this->parse($contents)) {
				return false;
			}
		}

		return true;
	}

	private function retrieve()
	{
		$client = new Client();
		$response = $client->get($this->vendor->getUrl());

		$body = $response->getBody();
		// if zip, extract it and retrieve contents
		if ('application/zip' == $response->getHeader('Content-Type')) {
			$tmpFile = tmpfile();
			fwrite($tmpFile, $body->getContents());

			$contents = '';
			$z = new \ZipArchive();
			if ($z->open(stream_get_meta_data($tmpFile)['uri'])) {
				$fileToOpen = null;
				for ($i = 0; $i < $z->numFiles; $i++) {
					if ($filename = $z->getNameIndex($i)) {
						if (preg_match('/(\.xml$)/', $filename)) {
							$fileToOpen = $filename;
							break;
						}
					}
				}

				if ($fileToOpen) {
					$fp = $z->getStream($fileToOpen);

					if ($fp) {
						while (!feof($fp)) {
							$contents .= fread($fp, 2);
						}
					}

					fclose($fp);
				}
			}

			fclose($tmpFile);

			if (!$contents) {
				return false;
			}
		} else {
			$contents = $body->getContents();
		}

		return $contents;
	}
	/**
	 * Parses feed using vendors own parse method
	 * @param  string $contents
	 * @return bool
	 */
	private function parse($contents)
	{
		$reader = new XML\Reader;
		$reader->xml($contents);
		$output = $reader->parse();

		$objects = $this->vendor->parse($output);
		if ($objects) {
			$this->objects = $objects;

			return true;
		}

		return false;
	}
	/**
	 * Gets all objects from this import
	 * @return array
	 */
	public function getObjects()
	{
		return $this->objects;
	}

	public function getUpdated($oldObjects)
	{
		$currentHashes = [];
		foreach ($oldObjects as $oldObject) {
			$currentHashes[$oldObject->vendorId] = $oldObject->getHash();
		}

		$updated = [];
		foreach ($this->objects as $object) {
			$objectUpdated = false;
			if (isset($currentHashes[$object->vendorId]) && $currentHashes[$object->vendorId] != $object->getHash()) {
				$objectUpdated = true;
				unset($currentHashes[$object->vendorId]);

			// new object
			} else {
				$objectUpdated = true;
			}

			if ($objectUpdated) {
				$updated[] = $object;
			}
		}

		return ['updated' => $updated, 'archived' => array_keys($currentHashes)];
	}
}
