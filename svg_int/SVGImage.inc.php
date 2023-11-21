<?php
//------------------------------------------------------------------------------------------------------------------------
// SVG Group node
//------------------------------------------------------------------------------------------------------------------------
namespace svg_int;
//------------------------------------------------------------------------------------------------------------------------
require_once('.root');

use svg_int\SVGNode;
use svg_int\SVGNodeTransformable;
//------------------------------------------------------------------------------------------------------------------------
class SVGImage extends SVGNode {

	use SVGNodeTransformable;

	public $is_transformable = True;

	public function __construct(\DOMNode &$node, \DOMDocument &$document) {
		// constructor
		parent::__construct($node, $document);
	}

	public function __destruct() {
		// destructor
		parent::__destruct();
	}

}
//------------------------------------------------------------------------------------------------------------------------
?>
