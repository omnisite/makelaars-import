<?php

namespace MakelaarsImport\Vendor;

class Demo extends \MakelaarsImport\Vendor
{
	public function setArguments($arguments)
	{
		return $this;
	}

	public function getUrl()
	{
		return 'http://makelaarsimport.localhost/Objecten_20141118.zip';
	}

	public function parse($parser)
	{
		$rw = new Realworks;
		return $rw->parse($parser);
	}
}