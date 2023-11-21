<?php
//------------------------------------------------------------------------------------------------------------------------
// SVG clipPath node
//	this node is container like <g>
//	it is not transformable itself, but all children could be transformed
//------------------------------------------------------------------------------------------------------------------------
namespace svg_int;
//------------------------------------------------------------------------------------------------------------------------
require_once('.root');

use svg_int\SVGNode;
use svg_int\SVGNodeTransformable;
//------------------------------------------------------------------------------------------------------------------------
class SVGClipPath extends SVGNode {
	
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

	public function set_transform($matrix): void {
		// set transform matrix to node from matrix3x3
		if($matrix) {
			foreach($this->children() as $clip_path_child_node) {
				$clip_path_child_node->set_transform($matrix);
			}
		}
	}
}
//------------------------------------------------------------------------------------------------------------------------
?>
