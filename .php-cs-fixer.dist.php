<?php

return (new PhpCsFixer\Config())
	->setRules([
		"@PSR2" => true,
		"indentation_type" => true,
		"no_extra_blank_lines" => true,
	])
	->setIndent("\t")
	->setFinder(
		PhpCsFixer\Finder::create()->in(__DIR__)
			->exclude("vendor")
			->exclude("libs")
	);
