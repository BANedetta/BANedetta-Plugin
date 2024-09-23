<?php

return (new PhpCsFixer\Config())
	->setRules([
		"@PSR2" => true,
		"indentation_type" => true,
		"no_extra_blank_lines" => true,
		"ordered_imports" => [
			"sort_algorithm" => "alpha",
			"imports_order" => ["class", "function", "const"],
		]
	])
	->setIndent("\t")
	->setFinder(
		PhpCsFixer\Finder::create()->in(__DIR__)
			// ->exclude("vendor")
			// ->exclude("libs")
	);
