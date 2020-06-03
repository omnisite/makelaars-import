<?php

namespace MakelaarsImport;

use GuzzleHttp\Client;
use Psr\Http\Message\MessageInterface;

class Import
{
	/**
	 * Vendor object
	 * @var WoonObject
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
        /** @var MessageInterface $response */
        $response = $client->request('GET', $this->vendor->getUrl());

		$body = $response->getBody();
		// if zip, extract it and retrieve contents
		if (in_array($response->getHeaderLine('Content-Type'), ['application/zip', 'application/x-zip;charset=utf-8'])) {
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
		// $reader = new XML\Reader;
		// $reader->xml($contents);
		// $output = $reader->parse();

		// $objects = $this->vendor->parse($output);
		$objects = $this->vendor->parse(new \SimpleXMLElement($contents));
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
			$currentHashes[$oldObject->vendor_id] = $oldObject->hash;
		}

		$updated = [];
		foreach ($this->objects as $object) {
			$objectUpdated = false;
			if (isset($currentHashes[$object->vendor_id])) {
				if ($currentHashes[$object->vendor_id] != $object->getHash()) {
					$objectUpdated = true;
				}

				// unset so that we can keep track of archived objects
				unset($currentHashes[$object->vendor_id]);

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
