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
		return '';
	}

	public function parse($parser)
	{
		$rw = new Realworks;
		return $rw->parse($parser);
	}
}