<?php

class Functions {

	// Remove URLs from test
	public static function cleanUrls($string) {
		return preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $string);
	}

	// Remove accents and make it lowercase
	public static function parseString($string) {

		$string = strtolower($string);
		$withAccents = array('/á/','/é/','/í/','/ó/','/ú/');
		$withoutAccents = array('a','e','i','o','u');
		$string = preg_replace($withAccents, $withoutAccents, $string);

		return $string;
	}

	// It checks if a keyword (or group, delimited by | ) is in a string
	public static function isKeywordInText($keyword, $text, $orDelimiter = '|') {

		$keywordArray = explode('|', $keyword);
		$matchesKeyword = false;

		foreach ($keywordArray as $singleKeyword) {
			if (strpos($text, $singleKeyword)!==FALSE) {
				$matchesKeyword = true;
			}
		}

		return $matchesKeyword;

	}

}