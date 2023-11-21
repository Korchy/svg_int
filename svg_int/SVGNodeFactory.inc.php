<?php
//------------------------------------------------------------------------------------------------------------------------
// SVG Node Factory - automatic conversion to Node based class
//------------------------------------------------------------------------------------------------------------------------
namespace svg_int;
//------------------------------------------------------------------------------------------------------------------------
require_once('.root');

use svg_int\SVGNode;
use svg_int\SVGClipPath;
use svg_int\SVGGroup;
use svg_int\SVGImage;
use svg_int\SVGPath;
use svg_int\SVGRect;
//------------------------------------------------------------------------------------------------------------------------
class SVGNodeFactory {

	public static function node(&$node, &$document) {
		// returns Node based object from $node
		if($node && isset($node->tagName)) {
			if($node->tagName == 'rect') {
				return new SVGRect($node, $document);
			} elseif($node->tagName == 'path') {
				return new SVGPath($node, $document);
			} elseif($node->tagName == 'g') {
				return new SVGGroup($node, $document);
			} elseif($node->tagName == 'image') {
				return new SVGImage($node, $document);
			} elseif($node->tagName == 'clipPath') {
				return new SVGClipPath($node, $document);
			}
		}
		return new SVGNode($node, $document);
	}
}
//------------------------------------------------------------------------------------------------------------------------
?>
