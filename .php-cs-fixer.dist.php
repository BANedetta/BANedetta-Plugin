<?php

require_once(dirname(__FILE__) ."/vendor/autoload.php");

$config = new PhpCsFixer\Config();
$config->setRules([
	"@PSR2" => true,
	"indentation_type" => true,
	"no_extra_blank_lines" => true,
]);
$config->setIndent("\t");
$config->setFinder(
	PhpCsFixer\Finder::create()
		->in(__DIR__)
);

return $config;
