<?php

namespace MakelaarsImport\Tests\Vendor;

use MakelaarsImport\Vendor\Realworks;
use Sabre\XML;

class RealworksTest extends \PHPUnit_Framework_TestCase
{
	public function testSetArguments()
	{
		$arguments = [
			'user' => 'test',
			'password' => 'test',
		];

		$rw = new Realworks;

		$this->assertInstanceOf('MakelaarsImport\Vendor\Realworks', $rw->setArguments($arguments));
	}

	public function testGetUrlReturnsString()
	{
		$rw = new Realworks([
			'user' => 'test',
			'password' => 'test',
		]);

		$this->assertInternalType('string', $rw->getUrl());
	}

	public function testParseReturnsArray()
	{
		$rw = new Realworks;

		$xmlString = file_get_contents('example.xml');
		$reader = new XML\Reader;
		$reader->xml($xmlString);
		$output = $reader->parse();

		$objects = $rw->parse($output);
		$this->assertInternalType('array', $objects);
	}
}
