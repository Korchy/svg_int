<?php
//------------------------------------------------------------------------------------------------------------------------
// SVG Rect node
//------------------------------------------------------------------------------------------------------------------------
namespace svg_int;
//------------------------------------------------------------------------------------------------------------------------
require_once('.root');

use svg_int\SVGAttribute;
use svg_int\SVGNode;
use svg_int\SVGNodeTransformable;
//------------------------------------------------------------------------------------------------------------------------
class SVGRect extends SVGNode {

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

	public function dimensions(): array {
		return [
			'x' => $this->attribute('x'),
			'y' => $this->attribute('y'),
			'width' => SVGAttribute::width($this->attribute('width')),
			'height' => SVGAttribute::width($this->attribute('height'))
		];
	}
}
//------------------------------------------------------------------------------------------------------------------------
?>
