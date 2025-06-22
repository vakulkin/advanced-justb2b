<?php

use Symfony\Component\Finder\Finder;

return [ 
	'prefix' => 'JustB2B',

	'finders' => [ 
		Finder::create()
			->files()
			->in( __DIR__ . '/vendor/htmlburger/carbon-fields' ),
	],
	'exclude-files' => array_map(
		static fn( $file ) => $file->getRealPath(),
		iterator_to_array(
			Symfony\Component\Finder\Finder::create()
				->files()
				->in( __DIR__ . '/vendor/htmlburger/carbon-fields/templates' )
		)
	),
	'exclude-classes' => array_merge(
		json_decode( file_get_contents( __DIR__ . '/vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-classes.json' ) ),
		json_decode( file_get_contents( __DIR__ . '/vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-interfaces.json' ) )
	),
	'exclude-functions' => json_decode( file_get_contents( __DIR__ . '/vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-functions.json' ) ),
	'exclude-constants' => json_decode( file_get_contents( __DIR__ . '/vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-constants.json' ) ),
	'patchers' => [ 
		static function (string $filePath, string $prefix, string $contents): string {
			// Direct string replacements for hook names
			$replacements = [ 
				"'carbon_" => "'justb2b_carbon_",
				'"carbon_' => '"justb2b_carbon_',
				"'crb_" => "'justb2b_crb_",
				'"crb_' => '"justb2b_crb_',
			];

			$contents = str_replace( array_keys( $replacements ), array_values( $replacements ), $contents );

			// Regex replacements for function declarations using double-quoted patterns
			$patterns = [ 
				"\bfunction\s+carbon_" => "function justb2b_carbon_",
				"\bfunction\s+crb_" => "function justb2b_crb_",
			];

			foreach ( $patterns as $pattern => $replacement ) {
				$contents = preg_replace( "/$pattern/", $replacement, $contents );
			}

			return $contents;
		},
	],
];
