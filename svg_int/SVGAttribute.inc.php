<?php
//------------------------------------------------------------------------------------------------------------------------
// SVG Attribute help class
//------------------------------------------------------------------------------------------------------------------------
namespace svg_int;
//------------------------------------------------------------------------------------------------------------------------
require_once('.root');

//------------------------------------------------------------------------------------------------------------------------
class SVGAttribute {

	private static $dimensions_available = ['cm', 'in', 'pt', 'pc', 'mm', 'px'];

	public static function width(string $attribute): float {
		// parse Width attribyte
		// remove dimensions
		$width = mb_ereg_replace(
			'#(' . implode('|', static::$dimensions_available) . ')$#',
			'',
			$attribute
		);
		// convert to int (maybe 1e3 format)
		return floatval($width);
	}

	public static function height(string $attribute): float {
		// parse Height attribyte
		// same algorithm as for Width
		return static::width($attribute);
	}

}
//------------------------------------------------------------------------------------------------------------------------
?>
