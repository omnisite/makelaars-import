<?php

namespace MakelaarsImport;

abstract class Vendor
{
	abstract protected function setArguments($arguments);
	abstract public function getUrl();
	abstract public function parse($parser);
}